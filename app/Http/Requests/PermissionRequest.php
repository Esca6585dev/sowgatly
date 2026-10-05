<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Admin panel permission (guard "admin"), named "<section>-<action>", e.g. "banner-create". */
class PermissionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    public function rules()
    {
        $permission = $this->route('permission');

        return [
            'name' => ['required', 'string', 'max:125', 'regex:/^[\pL\pN_.\- ]+$/u', Rule::unique('permissions', 'name')->where('guard_name', 'admin')->ignore(is_object($permission) ? $permission->id : $permission)],
        ];
    }
}
