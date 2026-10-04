<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * Products are stored per language (name_tm / name_en / name_ru,
     * description_tm / _en / _ru); the shop is always the caller's own,
     * so shop_id is never accepted from the client.
     */
    public function rules()
    {
        return [
            'name_tm' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_ru' => 'required|string|max:255',
            'description_tm' => 'required|string',
            'description_en' => 'required|string',
            'description_ru' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|integer|min:0|max:100',
            'production_time' => 'nullable|integer|min:0',
            'min_order' => 'nullable|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'seller_status' => 'required|boolean',
            'status' => 'required|boolean',
            'category_id' => 'required|exists:categories,id',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'integer|exists:brands,id',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string|starts_with:data:image/',
        ];
    }

    protected function prepareForValidation()
    {
        // Only normalise fields the client actually sent; adding null keys
        // would make "sometimes|required" rules fail on partial updates.
        $this->merge(array_filter([
            'brand_ids' => $this->decodeIfJson($this->brand_ids),
            'seller_status' => $this->transformToBoolean($this->seller_status),
            'status' => $this->transformToBoolean($this->status),
        ], fn ($value) => $value !== null));
    }

    private function decodeIfJson($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? $value;
        }

        return $value;
    }

    private function transformToBoolean($value)
    {
        if ($value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
