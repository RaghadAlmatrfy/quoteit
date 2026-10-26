<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Saves uploaded images / videos and builds their links.
 *
 * Locally they go to public/uploads (disk "uploads").
 * On the live server you can set MEDIA_DISK=supabase to keep them in Supabase Storage,
 * so they are not lost when the server restarts.
 */
class MediaUpload
{
    public static function disk(): string
    {
        return config('filesystems.media_disk', 'uploads');
    }

    /** @return array{media_path: string, media_type: string} */
    public static function store(UploadedFile $file, string $folder): array
    {
        return [
            'media_path' => $file->store($folder, self::disk()),
            'media_type' => str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'image',
        ];
    }

    public static function storeImage(UploadedFile $file, string $folder): string
    {
        return $file->store($folder, self::disk());
    }

    public static function url(?string $path): ?string
    {
        return $path ? Storage::disk(self::disk())->url($path) : null;
    }

    public static function delete(?string $path): void
    {
        if ($path) {
            Storage::disk(self::disk())->delete($path);
        }
    }
}
