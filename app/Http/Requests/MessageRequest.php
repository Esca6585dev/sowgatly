<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A contact message (table `messages`). Rules match the real columns.
 */
class MessageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'username' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'message' => 'required|string|max:5000',
            'user_id' => 'nullable|integer|exists:users,id',
        ];
    }
}
