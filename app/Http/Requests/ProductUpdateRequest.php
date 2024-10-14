<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'discount' => 'nullable|integer|min:0|max:100',
            'description' => 'required|string',
            'gender' => 'nullable|string',
            'sizes' => 'nullable',
            'separated_sizes' => 'nullable',
            'color' => 'nullable|string',
            'manufacturer' => 'nullable|string',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'production_time' => 'nullable|integer|min:0',
            'min_order' => 'nullable|integer|min:1',
            'seller_status' => 'required|boolean',
            'status' => 'required|boolean',
            'shop_id' => 'required|exists:shops,id',
            'category_id' => 'required|exists:categories,id',
            'brand_ids' => 'nullable|array',
            'images' => 'nullable|array',
            'images.*' => 'nullable|string|starts_with:data:image/'
        ];
    }

    protected function prepareForValidation()
    {
        $this->mergeIfMissing([
            'sizes' => $this->prepareSizes($this->sizes),
            'separated_sizes' => $this->prepareSizes($this->separated_sizes),
            'brand_ids' => $this->prepareSizes($this->brand_ids),
            'seller_status' => $this->transformToBoolean($this->seller_status),
            'status' => $this->transformToBoolean($this->status),
        ]);
    }

    private function prepareSizes($value)
    {
        if (is_string($value)) {
            return json_decode($value, true) ?? $value;
        }
        return $value;
    }

    private function transformToBoolean($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}