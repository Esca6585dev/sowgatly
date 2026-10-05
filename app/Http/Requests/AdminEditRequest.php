<?php

namespace App\Http\Requests;

/** Same as creating an admin, but the password may be left empty to keep the current one. */
class AdminEditRequest extends AdminCreateRequest
{
    protected function passwordRules(): string
    {
        return 'nullable|string|confirmed|min:8';
    }
}
