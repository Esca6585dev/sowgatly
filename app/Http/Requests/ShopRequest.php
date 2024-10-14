<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust this based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('shops')->ignore($this->shop)
            ],
            'mon_fri_open' => 'sometimes|required|date_format:H:i',
            'mon_fri_close' => 'sometimes|required|date_format:H:i|after:mon_fri_open',
            'sat_sun_open' => 'sometimes|required|date_format:H:i',
            'sat_sun_close' => 'sometimes|required|date_format:H:i|after:sat_sun_open',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'region_id' => 'sometimes|nullable|exists:regions,id',
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
            'mon_fri_close.after' => 'The closing time must be after the opening time for Monday to Friday.',
            'sat_sun_close.after' => 'The closing time must be after the opening time for Saturday and Sunday.',
        ];
    }
}