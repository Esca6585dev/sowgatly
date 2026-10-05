<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $category = $this->route('category');
        $type = $this->route('categoryType');

        return [
            'name_tm' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'name_ru' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
            'category_id' => [
                // A subcategory needs a parent; the "parent" list only makes parents.
                $type === 'sub' ? 'required' : 'nullable',
                'integer',
                // Two levels only: the parent must itself be a parent category, and not this one.
                Rule::exists('categories', 'id')->whereNull('category_id'),
                Rule::notIn(array_filter([is_object($category) ? $category->id : null])),
                function ($attribute, $value, $fail) use ($category) {
                    if ($value && is_object($category) && $category->categories()->exists()) {
                        $fail(__('A category with subcategories cannot become a subcategory.'));
                    }
                },
            ],
        ];
    }
}
