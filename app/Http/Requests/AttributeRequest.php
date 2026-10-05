<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttributeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /** Matches the attributes table: type, value, category_id. */
    public function rules()
    {
        return [
            'type' => 'required|string|max:255',
            'value' => 'required|string|max:255',
            'category_id' => 'nullable|integer|exists:categories,id',
        ];
    }
}
