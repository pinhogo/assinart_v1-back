<?php

namespace App\Http\Controllers\Api\Concerns;

use App\Models\Artist;
use App\Models\Button;
use Closure;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait InteractsWithMediaStorage
{
    protected function mediaDiskName(): string
    {
        $name = trim((string) config('filesystems.media_disk', env('MEDIA_DISK', 'public')));

        return $name !== '' ? $name : 'public';
    }

    protected function mediaDisk(): Filesystem
    {
        return Storage::disk($this->mediaDiskName());
    }

    protected function storeMediaFile(UploadedFile $file, string $directory): string
    {
        $diskName = $this->mediaDiskName();
        $path = $file->store($directory, $diskName);

        return Storage::disk($diskName)->url($path);
    }

    protected function extractRelativeStoragePath(?string $url): ?string
    {
        if (empty($url) || str_starts_with(trim($url), 'data:')) {
            return null;
        }

        $url = trim($url);
        $path = $this->stripAzureBase($url) ?? $this->localPathFromUrl($url);
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        $prefix = trim((string) config('filesystems.disks.azure.prefix', ''), '/');
        if ($prefix !== '' && str_starts_with($path, $prefix . '/')) {
            $path = substr($path, strlen($prefix) + 1);
        }

        if ($path === '' || in_array('..', explode('/', $path), true)) {
            return null;
        }

        return $path;
    }

    protected function deleteFileFromStorage(?string $url, ?int $ownerArtistId = null): void
    {
        $relativePath = $this->extractRelativeStoragePath($url);
        if (!$relativePath) {
            return;
        }

        $ownerId = $ownerArtistId ?? auth()->id();
        if ($ownerId !== null && $this->isStoragePathOwnedByAnotherArtist($relativePath, (int) $ownerId)) {
            return;
        }

        if ($this->mediaDisk()->exists($relativePath)) {
            $this->mediaDisk()->delete($relativePath);
        }

        if ($this->mediaDiskName() !== 'public' && str_contains((string) $url, '/storage/')
            && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }

    protected function isStorageUrl(string $url): bool
    {
        $trimmed = trim($url);
        if (str_starts_with($trimmed, '/storage/')) {
            return true;
        }

        $path = ltrim((string) parse_url($trimmed, PHP_URL_PATH), '/');

        return str_starts_with($path, 'storage/') || $this->stripAzureBase($trimmed) !== null;
    }

    protected function isStoragePathOwnedByAnotherArtist(string $relativePath, int $ownerId): bool
    {
        if (preg_match('#^(?:artists|(?:avatars|covers|wallpapers|cards))/(\d+)/#', $relativePath, $matches)) {
            if ((int) $matches[1] !== $ownerId) {
                return true;
            }
        }

        $likeSuffix = '%/' . $relativePath . '%';
        $artists = Artist::query()
            ->where('id', '!=', $ownerId)
            ->where(function ($query) use ($relativePath, $likeSuffix): void {
                foreach (['avatar_url', 'cover_url', 'wallpaper_url'] as $column) {
                    $query->orWhere($column, $relativePath)->orWhere($column, 'like', $likeSuffix);
                }
            })
            ->get(['avatar_url', 'cover_url', 'wallpaper_url']);

        foreach ($artists as $artist) {
            foreach (['avatar_url', 'cover_url', 'wallpaper_url'] as $column) {
                if ($this->extractRelativeStoragePath($artist->{$column}) === $relativePath) {
                    return true;
                }
            }
        }

        $buttons = Button::query()
            ->where('artist_id', '!=', $ownerId)
            ->where(fn ($q) => $q->where('image_url', $relativePath)->orWhere('image_url', 'like', $likeSuffix))
            ->get(['image_url']);

        foreach ($buttons as $button) {
            if ($this->extractRelativeStoragePath($button->image_url) === $relativePath) {
                return true;
            }
        }

        return false;
    }

    protected function validateStorageOwnership(string $attribute, mixed $value, ?int $ownerId, Closure $fail): void
    {
        if (!is_string($value) || trim($value) === '' || !$this->isStorageUrl($value)) {
            return;
        }

        $relativePath = $this->extractRelativeStoragePath($value);
        if ($relativePath === null || ($ownerId !== null && $this->isStoragePathOwnedByAnotherArtist($relativePath, $ownerId))) {
            $fail("O campo {$attribute} referencia um arquivo de mídia inválido ou pertencente a outro usuário.");
        }
    }

    private function localPathFromUrl(string $url): string
    {
        $parsed = parse_url($url, PHP_URL_PATH);

        return ($parsed !== false && $parsed !== null) ? $parsed : $url;
    }

    private function stripAzureBase(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === '') {
            return null;
        }

        $configured = (string) config('filesystems.disks.azure.url', '');
        if ($configured !== '' && str_starts_with($url, rtrim($configured, '/') . '/')) {
            return substr($url, strlen(rtrim($configured, '/')) + 1);
        }
        if (!str_ends_with($host, '.blob.core.windows.net')) {
            return null;
        }

        $path = ltrim((string) parse_url($url, PHP_URL_PATH), '/');
        $container = trim((string) config('filesystems.disks.azure.container', ''), '/');
        if ($container !== '' && str_starts_with($path, $container . '/')) {
            return substr($path, strlen($container) + 1);
        }

        return str_contains($path, '/') ? substr($path, strpos($path, '/') + 1) : $path;
    }
}
