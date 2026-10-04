<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Image;
use App\Models\Region;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Requests\ProductStoreRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ShopResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\CompositionResource;
use App\Http\Resources\BrandResource;
use Str;
use Storage;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="API Endpoints of Products"
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Get products for authenticated user",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="List of products for the authenticated user",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ProductResource")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            // Get the authenticated user
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            // Get products associated with the user's shop
            $products = Product::with(['category', 'shop', 'images', 'brands'])
                ->withRatingSummary()
                ->whereHas('shop', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->get();

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching products',
                'error' => $e->getMessage()
            ], 200);
        }
    }

     /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Create a new product with images",
     *     tags={"Products"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"name_tm","name_en","name_ru","price","description_tm","description_en","description_ru","seller_status","status","category_id"},
     *             @OA\Property(property="name_tm", type="string", example="Bägül çemeni"),
     *             @OA\Property(property="name_en", type="string", example="Rose bouquet"),
     *             @OA\Property(property="name_ru", type="string", example="Букет роз"),
     *             @OA\Property(property="price", type="number", format="float", example=29.99),
     *             @OA\Property(property="discount", type="integer", example=10),
     *             @OA\Property(property="description_tm", type="string", example="Gyzyl bägüller"),
     *             @OA\Property(property="description_en", type="string", example="Red roses"),
     *             @OA\Property(property="description_ru", type="string", example="Красные розы"),
     *             @OA\Property(property="stock", type="integer", example=20),
     *             @OA\Property(property="production_time", type="integer", example=300),
     *             @OA\Property(property="min_order", type="integer", example=1),
     *             @OA\Property(property="seller_status", type="boolean", example=true),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="shop_id", type="integer", example=1),
     *             @OA\Property(property="category_id", type="integer", example=3),
     *             @OA\Property(property="brand_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
    * @OA\Property(
    *     property="images",
    *     type="array",
    *     @OA\Items(type="string"),
    *     description="Array of base64 encoded images",
    *     example={
    *         "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAAyCAYAAAAZUZThAAAGz0lEQVR4nO2de4hVRRzHP3vdNDXNMnpYWWmUpZlp9iAigiKiKKQHQa+/IqLoQRQFRRBBf0UPISJK6EEUlVFEZFIUZWVlZfawzB6amrnmY3Vz94+Z4547d+6cM3PmnDnnnt8HFvbemTNzZn7z/c3v95uZC4ZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZ",
    *         "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAAyCAYAAAAZUZThAAAGz0lEQVR4nO2de4hVRRzHP3vdNDXNMnpYWWmUpZlp9iAigiKiKKQHQa+/IqLoQRQFRRBBf0UPISJK6EEUlVFEZFIUZWVlZfawzB6amrnmY3Vz94+Z4547d+6cM3PmnDnnnt8HFvbemTNzZn7z/c3v95uZC4ZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZ",
    *         "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAAyCAYAAAAZUZThAAAGz0lEQVR4nO2de4hVRRzHP3vdNDXNMnpYWWmUpZlp9iAigiKiKKQHQa+/IqLoQRQFRRBBf0UPISJK6EEUlVFEZFIUZWVlZfawzB6amrnmY3Vz94+Z4547d+6cM3PmnDnnnt8HFvbemTNzZn7z/c3v95uZC4ZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZ",
    *         "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAAyCAYAAAAZUZThAAAGz0lEQVR4nO2de4hVRRzHP3vdNDXNMnpYWWmUpZlp9iAigiKiKKQHQa+/IqLoQRQFRRBBf0UPISJK6EEUlVFEZFIUZWVlZfawzB6amrnmY3Vz94+Z4547d+6cM3PmnDnnnt8HFvbemTNzZn7z/c3v95uZC4ZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZ",
    *         "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAAyCAYAAAAZUZThAAAGz0lEQVR4nO2de4hVRRzHP3vdNDXNMnpYWWmUpZlp9iAigiKiKKQHQa+/IqLoQRQFRRBBf0UPISJK6EEUlVFEZFIUZWVlZfawzB6amrnmY3Vz94+Z4547d+6cM3PmnDnnnt8HFvbemTNzZn7z/c3v95uZC4ZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZ"
    *     }
    * )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product created successfully"),
     *             @OA\Property(property="product", ref="#/components/schemas/ProductResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(ProductStoreRequest $request)
    {
        $shop = $request->user()->shop;
        if (!$shop) {
            return $this->forbidden('Only shop owners can add products');
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();
            // A product always belongs to the caller's own shop, whatever
            // shop_id the client sent.
            $data['shop_id'] = $shop->id;

            $images = $data['images'] ?? [];
            $brandIds = $data['brand_ids'] ?? [];
            unset($data['images'], $data['brand_ids']);

            $product = Product::create($data);

            if (!empty($brandIds)) {
                $product->brands()->attach($brandIds);
            }

            // Handle image uploads
            foreach ($images as $base64Image) {
                $imageUrl = $this->uploadBase64Image($base64Image);
                $product->images()->create(['url' => $imageUrl]);
            }

            DB::commit();

            $resource = new ProductResource($product->load(['images', 'brands']));

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'product' => $resource,
                'data' => $resource,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Get a specific product",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Product details"),
     *     @OA\Response(response="404", description="Product not found")
     * )
     */
    public function show($id)
    {
        $product = Product::with('images')->withRatingSummary()->findOrFail($id);
        return new ProductResource($product);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Update an existing product with images",
     *     tags={"Products"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to update",
     *         @OA\Schema(type="integer", format="int64")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name_tm", type="string", example="Updated Bägül çemeni"),
     *             @OA\Property(property="name_en", type="string", example="Updated Rose bouquet"),
     *             @OA\Property(property="name_ru", type="string", example="Updated Букет роз"),
     *             @OA\Property(property="price", type="number", format="float", example=29.99),
     *             @OA\Property(property="discount", type="integer", example=10),
     *             @OA\Property(property="description_tm", type="string", example="Gyzyl bägüller"),
     *             @OA\Property(property="description_en", type="string", example="Red roses"),
     *             @OA\Property(property="description_ru", type="string", example="Красные розы"),
     *             @OA\Property(property="stock", type="integer", example=20),
     *             @OA\Property(property="production_time", type="integer", example=280),
     *             @OA\Property(property="min_order", type="integer", example=2),
     *             @OA\Property(property="seller_status", type="boolean", example=true),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="shop_id", type="integer", example=1),
     *             @OA\Property(property="category_id", type="integer", example=3),
     *             @OA\Property(property="brand_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3, 4}),
     *             @OA\Property(
     *                 property="images",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 description="Array of base64 encoded images",
     *                 example={
     *                     "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAAyCAYAAAAZUZThAAAGz0lEQVR4nO2de4hVRRzHP3vdNDXNMnpYWWmUpZlp9iAigiKiKKQHQa+/IqLoQRQFRRBBf0UPISJK6EEUlVFEZFIUZWVlZfawzB6amrnmY3Vz94+Z4547d+6cM3PmnDnnnt8HFvbemTNzZn7z/c3v95uZC4ZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZ",
     *                     "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAMgAAAAyCAYAAAAZUZThAAAGz0lEQVR4nO2de4hVRRzHP3vdNDXNMnpYWWmUpZlp9iAigiKiKKQHQa+/IqLoQRQFRRBBf0UPISJK6EEUlVFEZFIUZWVlZfawzB6amrnmY3Vz94+Z4547d+6cM3PmnDnnnt8HFvbemTNzZn7z/c3v95uZC4ZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZhGIZ"
     *                 }
     *             ),
     *             @OA\Property(property="_method", type="string", example="PUT"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product updated successfully"),
     *             @OA\Property(property="product", ref="#/components/schemas/ProductResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        if (!$this->ownsProduct($request, $product)) {
            return $this->forbidden('You can only edit products of your own shop');
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['shop_id'] = $product->shop_id;

            $images = $data['images'] ?? [];
            // Only touch the brand list when the client sent one.
            $brandIds = array_key_exists('brand_ids', $data) ? ($data['brand_ids'] ?? []) : null;
            unset($data['images'], $data['brand_ids']);

            $product->update($data);

            if ($brandIds !== null) {
                $product->brands()->sync($brandIds);
            }

            foreach ($images as $imageBase64) {
                if ($imageBase64) {
                    try {
                        $imagePath = $this->uploadBase64Image($imageBase64);
                        $product->images()->create(['url' => $imagePath]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to save image: ' . $e->getMessage());
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => new ProductResource($product->load(['images', 'brands']))
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function uploadBase64Image($base64Image)
    {
        // Extract the base64 encoded text without the prefix
        $image_parts = explode(";base64,", $base64Image);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);

        $fileName = uniqid() . '.' . $image_type;
        $filePath = 'public/product_images/' . $fileName;

        // Save file
        Storage::put($filePath, $image_base64);

        // Return the public URL
        return Storage::url($filePath);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Delete a product",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Product deleted successfully"),
     *     @OA\Response(response="404", description="Product not found")
     * )
     */
    public function destroy(Request $request, $id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            if (!$this->ownsProduct($request, $product)) {
                return $this->forbidden('You can only delete products of your own shop');
            }

            $this->deleteImages($product);

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the product',
                'error' => $e->getMessage()
            ], 200);
        }
    }

    protected function deleteImages($product)
    {
        $images = $product->images()->pluck('url');
        foreach($images as $image){
            if(Storage::exists($image)){
                Storage::delete($image);
            }
        }
    }
    /**
    * @OA\Get(
    *     path="/api/product/search",
    *     summary="Search for products",
    *     tags={"Products"},
    *     security={{"sanctum":{}}},
    *     @OA\Parameter(
    *         name="name",
    *         in="query",
    *         description="Search by product name",
    *         required=false,
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Parameter(
    *         name="min_price",
    *         in="query",
    *         description="Minimum price",
    *         required=false,
    *         @OA\Schema(type="number")
    *     ),
    *     @OA\Parameter(
    *         name="max_price",
    *         in="query",
    *         description="Maximum price",
    *         required=false,
    *         @OA\Schema(type="number")
    *     ),
    *     @OA\Parameter(
    *         name="category_id",
    *         in="query",
    *         description="Category ID",
    *         required=false,
    *         @OA\Schema(type="integer")
    *     ),
     *     @OA\Parameter(name="min_rating", in="query", required=false, description="Only products whose average rating is at least this (1-5)", @OA\Schema(type="number", format="float")),
     *     @OA\Parameter(name="delivery_today", in="query", required=false, description="1 = production time of three hours or less", @OA\Schema(type="integer", enum={0,1})),
     *     @OA\Parameter(name="sort", in="query", required=false, @OA\Schema(type="string", enum={"price_asc","price_desc","popular","newest"})),
    *     @OA\Response(
    *         response=200,
    *         description="Successful operation",
    *         @OA\JsonContent(
    *             @OA\Property(property="success", type="boolean"),
    *             @OA\Property(property="data", type="object"),
    *             @OA\Property(property="message", type="string")
    *         )
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Server error"
    *     )
    * )
    */
    public function search(Request $request)
    {
        try {
            $query = Product::query()->where('status', true);

            // Names are stored per language (name_tm / name_ru / name_en);
            // there is no plain `name` column, so match any of them.
            if ($request->filled('name')) {
                $term = '%' . $request->input('name') . '%';
                $query->where(function ($q) use ($term) {
                    $q->where('name_tm', 'like', $term)
                        ->orWhere('name_ru', 'like', $term)
                        ->orWhere('name_en', 'like', $term);
                });
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', $request->input('min_price'));
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', $request->input('max_price'));
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            if ($request->filled('shop_id')) {
                $query->where('shop_id', $request->input('shop_id'));
            }

            if ($request->filled('region_id')) {
                $this->filterByRegion($query, $request->input('region_id'));
            }

            if ($request->boolean('delivery_today')) {
                $query->deliveryToday();
            }

            $query->withRatingSummary();

            // min_rating filters on the average rating. A correlated subquery in
            // WHERE (instead of HAVING on the withAvg alias) also works for the
            // paginator's COUNT(*) wrapper on SQLite.
            if ($request->filled('min_rating')) {
                // Inlined as a float literal: a bound parameter arrives as text and
                // SQLite would then compare the numeric average against a string.
                $minRating = sprintf('%.2F', (float) $request->input('min_rating'));
                $query->whereRaw("(select avg(rating) from product_reviews where product_reviews.product_id = products.id) >= {$minRating}");
            }

            switch ($request->input('sort')) {
                case 'price_asc':
                    $query->orderBy('price');
                    break;
                case 'price_desc':
                    $query->orderByDesc('price');
                    break;
                case 'popular':
                    $query->popular();
                    break;
                default:
                    $query->latest();
            }

            $products = $query->with(['category', 'shop', 'images', 'brands', 'compositions'])->paginate(20);

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products->getCollection()),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'total' => $products->total(),
                ],
                'message' => 'Products retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching for products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/product/category/{category_id}",
     *     summary="Get products by category",
     *     tags={"Products"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="category_id",
     *         in="path",
     *         description="ID of category to return products for",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/ProductResource")
     *             ),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function getByCategory(Request $request, $category_id)
    {
        try {
            $category = Category::findOrFail($category_id);

            $query = Product::with([
                    'category',
                    'shop',
                    'images',
                    'brands',
                ])
                ->withRatingSummary()
                ->where('category_id', $category_id)
                ->where('status', true)
                ->orderBy('created_at', 'desc');

            if ($request->filled('region_id')) {
                $this->filterByRegion($query, $request->input('region_id'));
            }

            $products = $query->get();

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No products found in this category'
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => ProductResource::collection($products),
                'message' => 'Products retrieved successfully'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching products by category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function ownsProduct(Request $request, Product $product): bool
    {
        $shop = $request->user()->shop;

        return $shop && (int) $product->shop_id === (int) $shop->id;
    }

    private function forbidden(string $message)
    {
        return response()->json(['success' => false, 'message' => $message], 403);
    }

    private function filterByRegion($query, $regionId)
    {
        $regionIds = Region::selfAndDescendantIds($regionId);

        $query->whereHas('shop', function ($q) use ($regionIds) {
            $q->whereIn('region_id', $regionIds);
        });
    }
}
