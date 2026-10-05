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
    /**
     * Creating a shop needs the core fields; updating may send any subset,
     * so on PUT/PATCH the same rules are prefixed with "sometimes".
     */
    public function rules(): array
    {
        $prefix = $this->isMethod('POST') ? '' : 'sometimes|';

        return [
            'name' => $prefix.'required|string|max:255',
            'email' => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('shops')->ignore($this->shop),
            ],
            'mon_fri_open' => $prefix.'required|date_format:H:i',
            'mon_fri_close' => $prefix.'required|date_format:H:i|after:mon_fri_open',
            'sat_sun_open' => $prefix.'required|date_format:H:i',
            'sat_sun_close' => $prefix.'required|date_format:H:i|after:sat_sun_open',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'region_id' => 'sometimes|nullable|exists:regions,id',
            'phone' => 'sometimes|nullable|string|max:20',
            'delivery_fee' => 'sometimes|nullable|numeric|min:0|max:99999',
            'pickup_available' => 'sometimes|boolean',
            'min_order_amount' => 'sometimes|nullable|numeric|min:0',
            'description_tm' => 'sometimes|nullable|string|max:5000',
            'description_ru' => 'sometimes|nullable|string|max:5000',
            'description_en' => 'sometimes|nullable|string|max:5000',
            // Only the admin panel sends these two; the API ignores them.
            'status' => 'sometimes|in:pending,approved,rejected',
            'user_id' => 'sometimes|nullable|exists:users,id',
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
