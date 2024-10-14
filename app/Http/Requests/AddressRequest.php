<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AddressRequest",
 *     type="object",
 *     title="Address Request",
 *     description="Address request body data"
 * )
 */
class AddressRequest extends FormRequest
{
    /**
     * @OA\Property(
     *     property="shop_id",
     *     type="integer",
     *     description="ID of the shop",
     *     example=1
     * )
     *
     * @OA\Property(
     *     property="address_name",
     *     type="string",
     *     description="Name of the address",
     *     example="Main Office"
     * )
     *
     * @OA\Property(
     *     property="postal_code",
     *     type="string",
     *     description="Postal code",
     *     example="12345"
     * )
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'shop_id' => 'required|integer|exists:shops,id',
            'address_name' => 'required|string|max:255',
            'postal_code' => 'required|string|max:20',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'shop_id.required' => 'The shop ID is required.',
            'shop_id.integer' => 'The shop ID must be an integer.',
            'shop_id.exists' => 'The selected shop does not exist.',
            'address_name.required' => 'The address name is required.',
            'address_name.max' => 'The address name may not be greater than 255 characters.',
            'postal_code.required' => 'The postal code is required.',
            'postal_code.max' => 'The postal code may not be greater than 20 characters.',
        ];
    }
}