<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Admin panel product form. Products are stored per language.
 */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
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
            'stock' => 'nullable|integer|min:0',
            'production_time' => 'nullable|integer|min:0',
            'min_order' => 'nullable|integer|min:1',
            'shop_id' => 'required|exists:shops,id',
            'category_id' => 'required|exists:categories,id',
            'status' => 'required|boolean',
            'seller_status' => 'required|boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'discount' => $this->discount === '' ? null : $this->discount,
            'stock' => $this->stock === '' ? null : $this->stock,
            'production_time' => $this->production_time === '' ? null : $this->production_time,
            'min_order' => $this->min_order === '' ? null : $this->min_order,
        ]);
    }
}
