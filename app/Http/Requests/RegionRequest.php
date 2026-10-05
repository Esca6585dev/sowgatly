<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $region = $this->route('region');

        return [
            'name' => 'required|string|max:255',
            'type' => 'required|in:country,province,city,village',
            'parent_id' => [
                'nullable', 'integer', 'exists:regions,id',
                // A region cannot be its own parent.
                Rule::notIn(array_filter([optional($region)->id])),
            ],
        ];
    }
}
