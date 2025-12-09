<?php

namespace App\Services\Images;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ImageService
{
    public function storeProductImage(?UploadedFile $image): ?string
    {
        return $this->store($image, 'products');
    }

    public function storeStockImage(?UploadedFile $image): ?string
    {
        return $this->store($image, 'stocks');
    }

    public function storePlaceholder(string $directory): string
    {
        $filename = Str::random(40) . '.png';
        $path = "{$directory}/{$filename}";

        $pixel = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9YpWXC8AAAAASUVORK5CYII='
        );

        // If Cloudinary PHP SDK is available and credentials are present, upload the
        // placeholder to Cloudinary and return the remote URL. Otherwise store in
        // the local public disk and return the local path.
        if (class_exists('Cloudinary\\Uploader') && env('CLOUDINARY_API_KEY')) {
            $data = 'data:image/png;base64,' . base64_encode($pixel);
            try {
                $result = call_user_func(['Cloudinary\\Uploader', 'upload'], $data, ['folder' => $directory]);
                return $result['secure_url'] ?? $result['url'] ?? $path;
            } catch (\Throwable $e) {
                // Fall through to local storage on error
            }
        }

        Storage::disk('public')->put($path, $pixel);

        return $path;
    }

    public function delete(?string $path): void
    {
        if (! $path) {
            return;
        }

        // If the stored path looks like a Cloudinary URL and the SDK is present,
        // try to delete the remote resource by public_id extracted from URL.
        if (class_exists('Cloudinary\\Uploader') && str_contains($path, 'res.cloudinary.com')) {
            // Extract public id from typical Cloudinary URL
            if (preg_match('#/image/upload/(?:v\d+/)?(.+)\.[a-zA-Z0-9]+$#', $path, $m)) {
                $publicId = $m[1];
                try {
                    call_user_func(['Cloudinary\\Uploader', 'destroy'], $publicId);
                    return;
                } catch (\Throwable $e) {
                    // ignore and fall back to local delete attempt
                }
            }
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    protected function store(?UploadedFile $image, string $directory): ?string
    {
        if (! $image) {
            return null;
        }

        // Prefer Cloudinary if the SDK is available and credentials are set.
        if (class_exists('Cloudinary\\Uploader') && env('CLOUDINARY_API_KEY')) {
            $tmpPath = $image->getPathname();
            try {
                $result = call_user_func(['Cloudinary\\Uploader', 'upload'], $tmpPath, ['folder' => $directory]);
                // Return the secure URL if available, otherwise fall back to public_id
                return $result['secure_url'] ?? $result['url'] ?? ($result['public_id'] ?? null);
            } catch (\Throwable $e) {
                // Fall back to local storage on any upload error
            }
        }

        return $image->store($directory, 'public');
    }



    public function deleteProductImage(?string $path): void
    {
        if (!$path) return;

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function deleteStockImage(?string $path): void
    {
        if (!$path) return;

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
