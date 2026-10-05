<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Admin panel role (guard "admin") with its permissions. */
class RoleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $role = $this->route('role');

        return [
            'name' => ['required', 'string', 'max:125', Rule::unique('roles', 'name')->where('guard_name', 'admin')->ignore(is_object($role) ? $role->id : $role)],
            'permissions' => 'nullable|array',
            'permissions.*' => ['string', Rule::exists('permissions', 'name')->where('guard_name', 'admin')],
        ];
    }
}
