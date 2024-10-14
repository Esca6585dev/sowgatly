<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ProductResource",
 *     title="Product",
 *     description="Product model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Stylish T-Shirt"),
 *     @OA\Property(property="price", type="number", format="float", example=29.99),
 *     @OA\Property(property="discount", type="integer", nullable=true, example=10),
 *     @OA\Property(property="description", type="string", example="A comfortable and stylish t-shirt for everyday wear."),
 *     @OA\Property(property="gender", type="string", nullable=true, example="Unisex"),
 *     @OA\Property(property="sizes", type="array", @OA\Items(type="string"), nullable=true, example={"S", "M", "L", "XL"}),
 *     @OA\Property(property="separated_sizes", type="array", @OA\Items(type="string"), nullable=true, example={"S", "M", "L", "XL"}),
 *     @OA\Property(property="color", type="string", nullable=true, example="Blue"),
 *     @OA\Property(property="manufacturer", type="string", nullable=true, example="FashionCo"),
 *     @OA\Property(property="width", type="number", format="float", nullable=true, example=30.5),
 *     @OA\Property(property="height", type="number", format="float", nullable=true, example=50.0),
 *     @OA\Property(property="weight", type="number", format="float", nullable=true, example=0.2),
 *     @OA\Property(property="production_time", type="integer", nullable=true, example=5),
 *     @OA\Property(property="min_order", type="integer", nullable=true, example=1),
 *     @OA\Property(property="seller_status", type="boolean", example=true),
 *     @OA\Property(property="status", type="boolean", example=true),
 *     @OA\Property(property="shop_id", type="integer", example=1),
 *     @OA\Property(property="category_id", type="integer", example=3),
 *     @OA\Property(property="brand_ids", type="array", @OA\Items(type="integer"), nullable=true, example={1, 2}),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-06-15T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-06-15T12:00:00Z"),
 *     @OA\Property(
 *         property="images",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ImageResource"),
 *         description="Associated images"
 *     ),
 *     @OA\Property(
 *         property="category",
 *         ref="#/components/schemas/CategoryResource",
 *         description="Associated category"
 *     ),
 *     @OA\Property(
 *         property="shop",
 *         ref="#/components/schemas/ShopResource",
 *         description="Associated shop"
 *     ),
 *     @OA\Property(
 *         property="brands",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/BrandResource"),
 *         description="Associated brands"
 *     ),
 *     @OA\Property(
 *         property="compositions",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/CompositionResource"),
 *         description="Associated compositions"
 *     )
 * )
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'description' => $this->description,
            'gender' => $this->gender,
            'sizes' => json_decode($this->sizes),
            'separated_sizes' => json_decode($this->separated_sizes),
            'color' => $this->color,
            'manufacturer' => $this->manufacturer,
            'width' => $this->width,
            'height' => $this->height,
            'weight' => $this->weight,
            'production_time' => $this->production_time,
            'min_order' => $this->min_order,
            'seller_status' => $this->seller_status,
            'status' => $this->status,
            'shop_id' => $this->shop_id,
            'category_id' => $this->category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'brands' => $this->brands,
            'images' => $this->images,
            'category' => $this->category,
            'shop' => $this->shop,
        ];
    }
}