<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'discount' => 'required|numeric|min:0|max:100',
            'category_id' => 'required|exists:categories,id',
            'shop_id' => 'required|exists:shops,id',
        ];

        // Make images optional
        $rules['images'] = 'sometimes|nullable|array';
        
        // If images are provided, validate each image
        $rules['images.*'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';

        return $rules;
    }
}