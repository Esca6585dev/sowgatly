<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\FavoriteCollection;
use App\Models\FavoriteCollectionItem;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Named favorite lists ("Подборки"). The implicit favorites list stays on
 * /api/favorites; these are extra, user-named collections.
 *
 * Another user's collection is answered with 404, never 403, so ids leak nothing.
 */
class FavoriteCollectionController extends Controller
{
    use RespondsWithJson;

    /**
     * @OA\Get(
     *     path="/api/me/collections",
     *     summary="The caller's favorite collections with cover images",
     *     tags={"Collections"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Collections ordered by position",
     *         @OA\JsonContent(@OA\Property(property="success", type="boolean"), @OA\Property(property="data", type="array", @OA\Items(
     *             @OA\Property(property="id", type="integer"), @OA\Property(property="name", type="string"),
     *             @OA\Property(property="position", type="integer"), @OA\Property(property="items_count", type="integer"),
     *             @OA\Property(property="covers", type="array", @OA\Items(type="string"), description="Up to four product image URLs"),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )))
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $collections = FavoriteCollection::where('user_id', $request->user()->id)
            ->withCount('items')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return $this->ok(['data' => $collections->map(fn ($c) => $this->summary($c))->values()]);
    }

    /**
     * @OA\Post(
     *     path="/api/me/collections",
     *     summary="Create a collection",
     *     tags={"Collections"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(@OA\JsonContent(required={"name"}, @OA\Property(property="name", type="string", maxLength=100, example="Что я хочу"))),
     *     @OA\Response(response="201", description="Created"),
     *     @OA\Response(response="422", description="Duplicate name or too many collections")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:100', Rule::unique('favorite_collections')->where('user_id', $userId)],
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        if (FavoriteCollection::where('user_id', $userId)->count() >= FavoriteCollection::MAX_PER_USER) {
            return $this->fail('You can have at most ' . FavoriteCollection::MAX_PER_USER . ' collections');
        }

        $position = (int) FavoriteCollection::where('user_id', $userId)->max('position') + 1;
        $collection = FavoriteCollection::create(['user_id' => $userId, 'name' => trim($request->input('name')), 'position' => $position]);
        $collection->loadCount('items');

        return $this->ok(['data' => $this->summary($collection)], null, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/me/collections/{id}",
     *     summary="A collection with its products (paginated, 20 per page)",
     *     tags={"Collections"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Collection and products"),
     *     @OA\Response(response="404", description="Not found")
     * )
     */
    public function show(Request $request, $id): JsonResponse
    {
        $collection = $this->find($request, $id);
        if (!$collection) {
            return $this->fail('Collection not found', 404);
        }

        $products = $collection->products()
            ->with(['category', 'shop', 'images'])
            ->withRatingSummary()
            ->orderByDesc('favorite_collection_items.created_at')
            ->paginate(20);

        $collection->loadCount('items');

        return $this->ok([
            'data' => $this->summary($collection) + [
                'products' => ProductResource::collection($products->getCollection())->resolve(),
            ],
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/me/collections/{id}",
     *     summary="Rename or reorder a collection",
     *     tags={"Collections"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(@OA\Property(property="name", type="string", maxLength=100), @OA\Property(property="position", type="integer"))),
     *     @OA\Response(response="200", description="Updated"),
     *     @OA\Response(response="404", description="Not found")
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $collection = $this->find($request, $id);
        if (!$collection) {
            return $this->fail('Collection not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'required', 'string', 'max:100',
                Rule::unique('favorite_collections')->where('user_id', $collection->user_id)->ignore($collection->id)],
            'position' => 'sometimes|integer|min:0|max:10000',
        ]);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $collection->fill($validator->validated());
        if ($request->has('name')) {
            $collection->name = trim($request->input('name'));
        }
        $collection->save();
        $collection->loadCount('items');

        return $this->ok(['data' => $this->summary($collection)]);
    }

    /**
     * @OA\Delete(
     *     path="/api/me/collections/{id}",
     *     summary="Delete a collection (its products stay in the catalog and in /favorites)",
     *     tags={"Collections"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Deleted"),
     *     @OA\Response(response="404", description="Not found")
     * )
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $collection = $this->find($request, $id);
        if (!$collection) {
            return $this->fail('Collection not found', 404);
        }

        $collection->delete();

        return $this->ok();
    }

    /**
     * @OA\Post(
     *     path="/api/me/collections/{id}/products",
     *     summary="Add a product to a collection (idempotent)",
     *     tags={"Collections"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(required={"product_id"}, @OA\Property(property="product_id", type="integer"))),
     *     @OA\Response(response="201", description="Added"),
     *     @OA\Response(response="200", description="Was already in the collection"),
     *     @OA\Response(response="404", description="Not found"),
     *     @OA\Response(response="422", description="Collection is full")
     * )
     */
    public function addProduct(Request $request, $id): JsonResponse
    {
        $collection = $this->find($request, $id);
        if (!$collection) {
            return $this->fail('Collection not found', 404);
        }

        $validator = Validator::make($request->all(), ['product_id' => 'required|integer|exists:products,id']);
        if ($validator->fails()) {
            return $this->fail($validator->errors()->first());
        }

        $exists = FavoriteCollectionItem::where('collection_id', $collection->id)
            ->where('product_id', $request->input('product_id'))
            ->exists();
        if ($exists) {
            return $this->ok(['added' => false]);
        }

        if ($collection->items()->count() >= FavoriteCollection::MAX_ITEMS) {
            return $this->fail('This collection is full');
        }

        FavoriteCollectionItem::create(['collection_id' => $collection->id, 'product_id' => $request->input('product_id')]);

        return $this->ok(['added' => true], null, 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/me/collections/{id}/products/{product_id}",
     *     summary="Remove a product from a collection",
     *     tags={"Collections"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="product_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response="200", description="Removed"),
     *     @OA\Response(response="404", description="Not found")
     * )
     */
    public function removeProduct(Request $request, $id, $productId): JsonResponse
    {
        $collection = $this->find($request, $id);
        if (!$collection) {
            return $this->fail('Collection not found', 404);
        }

        $deleted = FavoriteCollectionItem::where('collection_id', $collection->id)
            ->where('product_id', $productId)
            ->delete();

        return $deleted ? $this->ok() : $this->fail('Product is not in this collection', 404);
    }

    private function find(Request $request, $id): ?FavoriteCollection
    {
        return FavoriteCollection::where('user_id', $request->user()->id)->find($id);
    }

    /** List-row shape: counts plus up to four cover images (newest items first). */
    private function summary(FavoriteCollection $collection): array
    {
        $productIds = FavoriteCollectionItem::where('collection_id', $collection->id)
            ->latest()->limit(4)->pluck('product_id');

        $covers = Image::whereIn('product_id', $productIds)
            ->whereNotNull('url')
            ->get()
            ->unique('product_id')
            ->sortBy(fn ($image) => $productIds->search($image->product_id))
            ->pluck('url')
            ->values();

        return [
            'id' => $collection->id,
            'name' => $collection->name,
            'position' => $collection->position,
            'items_count' => (int) ($collection->items_count ?? $collection->items()->count()),
            'covers' => $covers,
            'created_at' => $collection->created_at,
        ];
    }
}
