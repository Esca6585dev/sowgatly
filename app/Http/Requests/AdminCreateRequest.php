<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminCreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $admin = $this->route('admin');
        $ignore = is_object($admin) ? $admin->id : $admin;

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('admins', 'username')->ignore($ignore)],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($ignore)],
            'password' => $this->passwordRules(),
            'roles' => 'nullable|array',
            'roles.*' => ['string', Rule::exists('roles', 'name')->where('guard_name', 'admin')],
        ];
    }

    protected function passwordRules(): string
    {
        return 'required|string|confirmed|min:8';
    }
}
