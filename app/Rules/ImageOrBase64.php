<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ImageOrBase64 implements Rule
{
    public function passes($attribute, $value)
    {
        // Check if it's a valid image file
        if (is_file($value)) {
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $value);
            finfo_close($fileInfo);
            
            return in_array($mimeType, $allowedMimeTypes);
        }

        // Check if it's a valid base64 image
        if (is_string($value)) {
            $base64regex = '/^data:image\/(\w+);base64,/';
            return preg_match($base64regex, $value);
        }

        return false;
    }

    public function message()
    {
        return 'The :attribute must be a valid image file or a base64 encoded image.';
    }
}