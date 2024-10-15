<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\Address;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Http\Resources\ShopResource;
use App\Http\Requests\ShopRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Rules\ImageOrBase64;

/**
 * @OA\Tag(
 *     name="Shops",
 *     description="API Endpoints of shops"
 * )
 */
class ShopController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/shops",
     *     summary="Get all shops",
     *     tags={"Shops"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ShopResource")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $shops = Shop::where('user_id', auth()->user()->id)->get();

        return ShopResource::collection($shops);
    }

    /**
     * @OA\Post(
     *     path="/api/shops",
     *     tags={"Shops"},
     *     security={{"sanctum":{}}},
     *     summary="Create a new shop",
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "mon_fri_open", "mon_fri_close", "sat_sun_open", "sat_sun_close"},
     *                 @OA\Property(property="name", type="string", example="Modahouse"),
     *                 @OA\Property(property="email", type="string", example="modahouse@modahouse.top", nullable=true),
     *                 @OA\Property(property="mon_fri_open", type="string", format="time", example="09:00"),
     *                 @OA\Property(property="mon_fri_close", type="string", format="time", example="18:00"),
     *                 @OA\Property(property="sat_sun_open", type="string", format="time", example="10:00"),
     *                 @OA\Property(property="sat_sun_close", type="string", format="time", example="16:00"),
     *                 @OA\Property(property="image", type="string", format="binary", nullable=true),
     *                 @OA\Property(property="region_id", type="integer", example=1, nullable=true),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Shop created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/ShopResource")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An error occurred while creating the shop.")
     *         )
     *     )
     * )
     */
    public function store(ShopRequest $request)
    {
        DB::beginTransaction();

        try {
            $validatedData = $request->validated();

            $shop = Shop::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'] ?? null,
                'mon_fri_open' => $validatedData['mon_fri_open'],
                'mon_fri_close' => $validatedData['mon_fri_close'],
                'sat_sun_open' => $validatedData['sat_sun_open'],
                'sat_sun_close' => $validatedData['sat_sun_close'],
                'region_id' => $validatedData['region_id'] ?? null,
                'user_id' => Auth::id(),
            ]);

            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('shop_images', 'public');
                $shop->image = $imagePath;
                $shop->save();
            }

            DB::commit();

            $shop = $shop->fresh('region');

            return new ShopResource($shop);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while creating the shop: ' . $e->getMessage()], 500);
        }
    }
    /**
     * @OA\Get(
     *     path="/api/shops/{id}",
     *     tags={"Shops"},
     *     security={{"sanctum":{}}},
     *     summary="Get a shop",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200", 
     *         description="Shop details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ShopResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Shop not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Shop not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        $shop = Shop::with('address', 'region')->find($id);

        if (!$shop) {
            return response()->json([
                'status' => false,
                'message' => 'Shop not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => new ShopResource($shop)
        ], 200);
    }
    /**
     * @OA\Post(
     *     path="/api/shops/{id}",
     *     tags={"Shops"},
     *     security={{"sanctum":{}}},
     *     summary="Update a shop",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Updated Modahouse"),
     *                 @OA\Property(property="email", type="string", example="updated@example.com"),
     *                 @OA\Property(property="mon_fri_open", type="string", example="08:00"),
     *                 @OA\Property(property="mon_fri_close", type="string", example="19:00"),
     *                 @OA\Property(property="sat_sun_open", type="string", example="09:00"),
     *                 @OA\Property(property="sat_sun_close", type="string", example="17:00"),
     *                 @OA\Property(property="image", type="file"),
     *                 @OA\Property(property="region_id", type="integer", example=2),
     *                 @OA\Property(property="_method", type="string", example="PUT"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200", 
     *         description="Shop updated",
     *         @OA\JsonContent(ref="#/components/schemas/ShopResource")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized action",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Shop not found",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server Error",
     *     )
     * )
     */
    public function update(ShopRequest $request, Shop $shop)
    {
        // Check if the authenticated user owns this shop
        if ($request->user()->id !== $shop->user_id) {
            return response()->json(['error' => 'You do not have permission to update this shop'], 403);
        }

        DB::beginTransaction();

        try {
            $shop->update($request->validated());

            if ($request->hasFile('image')) {
                $this->handleImageUpload($shop, $request->file('image'));
            }

            DB::commit();

            return new ShopResource($shop->load('region'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'An error occurred while updating the shop: ' . $e->getMessage()], 500);
        }
    }

    private function handleImageUpload(Shop $shop, $imageFile)
    {
        if ($shop->image) {
            Storage::disk('public')->delete($shop->image);
        }
        
        $shopSlug = Str::slug($shop->name);
        $dateFolder = now()->format('d-m-Y-H-i-s');
        $randomString = Str::random(10);
        $extension = $imageFile->getClientOriginalExtension();
        
        $path = "shop/{$shopSlug}-{$dateFolder}/{$randomString}.{$extension}";
        
        Storage::disk('public')->put($path, file_get_contents($imageFile));
        
        $shop->image = $path;
        $shop->save();
    }
    /**
     * @OA\Delete(
     *     path="/api/shops/{id}",
     *     tags={"Shops"},
     *     security={{"sanctum":{}}},
     *     summary="Delete a shop",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Shop deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Shop not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     )
     * )
     */
    public function destroy($id)
    {
        $shop = Shop::find($id);

        if (!$shop) {
            return response()->json([
                'status' => false,
                'message' => 'Shop not found'
            ], 404);
        }

        try {
            if ($shop->image) {
                Storage::disk('public')->delete($shop->image);
            }
            $shop->delete();
            return response()->json([
                'status' => true,
                'message' => 'Shop deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete shop: ' . $e->getMessage()
            ], 500);
        }
    }
}