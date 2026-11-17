<?php

namespace App\Support;

class Brand
{
    public static function logoUrl(): ?string
    {
        foreach (['svg', 'png', 'webp', 'jpg', 'jpeg'] as $extension) {
            $path = 'images/iub-logo.'.$extension;

            if (is_file(public_path($path))) {
                return asset($path);
            }
        }

        return null;
    }
}