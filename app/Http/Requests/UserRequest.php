<?php

namespace App\Http\Requests;

use App\Rules\TurkmenistanPhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Admin panel: create / edit a customer. (The API uses UserStoreRequest / UserUpdateRequest.) */
class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Accept "+993 65 12-34-56" as well as "65123456".
        $phone = preg_replace('/\D+/', '', (string) $this->input('phone_number'));
        if (strlen($phone) === 11 && str_starts_with($phone, '993')) {
            $phone = substr($phone, 3);
        }

        $this->merge([
            'phone_number' => $phone,
            'email' => $this->filled('email') ? trim($this->input('email')) : null,
        ]);
    }

    public function rules()
    {
        $user = $this->route('user');
        $ignore = is_object($user) ? $user->id : $user;

        return [
            'name' => 'required|string|max:255',
            'phone_number' => ['required', new TurkmenistanPhoneNumber, Rule::unique('users', 'phone_number')->ignore($ignore)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($ignore)],
            'birth_date' => 'nullable|date|before:today',
            'status' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:4096',
        ];
    }
}
