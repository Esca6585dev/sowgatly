<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class TurkmenistanPhoneNumber implements Rule
{
    public function passes($attribute, $value)
    {
        return preg_match('/^[0-9]{8}$/', $value) === 1;
    }

    public function message()
    {
        return 'The :attribute must be exactly 8 digits.';
    }
}