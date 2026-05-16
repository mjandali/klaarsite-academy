<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class WebpImageStore
{
    public function store(UploadedFile $file, string $directory = 'course-thumbnails', string $disk = 'public'): string
    {
        return $this->storeWithMetadata($file, $directory, $disk)['path'];
    }

    /**
     * @return array{path: string, mime_type: string, size: int, width: int, height: int}
     */
    public function storeWithMetadata(UploadedFile $file, string $directory = 'course-thumbnails', string $disk = 'public'): array
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            throw new RuntimeException('WebP conversion is not available on this server.');
        }

        $contents = $file->get();
        $image = @imagecreatefromstring($contents);

        if (! $image) {
            throw new RuntimeException('The uploaded image could not be processed.');
        }

        $width = imagesx($image);
        $height = imagesy($image);

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, 85);
        $webp = ob_get_clean();
        imagedestroy($image);

        if ($webp === false || $webp === null) {
            throw new RuntimeException('The uploaded image could not be converted to WebP.');
        }

        $path = trim($directory, '/').'/'.Str::uuid().'.webp';
        Storage::disk($disk)->put($path, $webp);

        return [
            'path' => $path,
            'mime_type' => 'image/webp',
            'size' => strlen($webp),
            'width' => $width,
            'height' => $height,
        ];
    }
}
