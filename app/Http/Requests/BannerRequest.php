<?php

namespace App\Http\Requests;

use App\Models\Banner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title_tm' => 'required|string|max:255',
            'title_ru' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'subtitle_tm' => 'nullable|string|max:255',
            'subtitle_ru' => 'nullable|string|max:255',
            'subtitle_en' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'link_type' => ['required', Rule::in(Banner::LINK_TYPES)],
            'link_value' => 'nullable|string|max:500|required_unless:link_type,none',
            'region_id' => 'nullable|exists:regions,id',
            'position' => 'nullable|integer|min:0|max:1000',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'position' => $this->input('position', 0) === '' ? 0 : $this->input('position', 0),
        ]);
    }
}
