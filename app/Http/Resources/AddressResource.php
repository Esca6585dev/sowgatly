<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AddressResource",
 *     title="Address Resource",
 *     description="Address resource",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Address ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="shop_id",
 *         type="integer",
 *         description="Shop ID",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="address_name",
 *         type="string",
 *         description="Address name",
 *         example="123 Main St, Anytown"
 *     ),
 *     @OA\Property(
 *         property="postal_code",
 *         type="string",
 *         description="Postal code",
 *         example="744000"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation date and time",
 *         example="2023-06-07T12:00:00Z"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Last update date and time",
 *         example="2023-06-07T12:00:00Z"
 *     )
 * )
 */
class AddressResource extends JsonResource
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
            'shop_id' => $this->shop_id,
            'address_name' => $this->address_name,
            'postal_code' => $this->postal_code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}