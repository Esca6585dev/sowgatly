<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductBrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     *
     * @OA\Schema(
     *     schema="ProductBrandResource",
     *     type="object",
     *     title="Brand Resource",
     *     @OA\Property(
     *         property="products",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/ProductResource"),
     *         description="Associated products"
     *     ),
     *     @OA\Property(
     *         property="brands",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/BrandResource"),
     *         description="Associated brands"
     *     ),
     * )
     */
    public function toArray($request)
    {
        return [
            'products' => ProductResource::collection($this->whenLoaded('products')),
            'brands' => BrandResource::collection($this->whenLoaded('brands')),
        ];
    }
}