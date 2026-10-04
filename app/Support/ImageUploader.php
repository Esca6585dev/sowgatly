<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores an image that arrived either as a multipart file or as a
 * "data:image/...;base64," string on the public disk and returns the path
 * that asset() can serve (storage/<dir>/<file>).
 */
class ImageUploader
{
    public static function store($image, string $directory): string
    {
        $directory = trim($directory, '/');

        if ($image instanceof UploadedFile) {
            $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
            $fileName = Str::random(20) . '.' . $extension;
            $image->storeAs($directory, $fileName, 'public');

            return 'storage/' . $directory . '/' . $fileName;
        }

        if (is_string($image) && preg_match('/^data:image\/(\w+);base64,/', $image, $matches)) {
            $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
            $binary = base64_decode(substr($image, strpos($image, ',') + 1), true);
            if ($binary === false) {
                throw new \InvalidArgumentException('Invalid base64 image');
            }
            $fileName = Str::random(20) . '.' . $extension;
            Storage::disk('public')->put($directory . '/' . $fileName, $binary);

            return 'storage/' . $directory . '/' . $fileName;
        }

        throw new \InvalidArgumentException('Unsupported image payload');
    }

    /**
     * Delete a file previously returned by store(). Paths outside the public
     * disk (seeded defaults such as img/logo/...) are left alone.
     */
    public static function delete(?string $path): void
    {
        if ($path && Str::startsWith($path, 'storage/')) {
            Storage::disk('public')->delete(Str::after($path, 'storage/'));
        }
    }
}
