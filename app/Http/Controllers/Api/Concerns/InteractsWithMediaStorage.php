<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait InteractsWithMediaStorage
{
    /**
     * Nome do disco de mídia ativo (MEDIA_DISK). Usa 'public' quando vazio.
     */
    protected function mediaDiskName(): string
    {
        $name = trim((string) config('filesystems.media_disk', env('MEDIA_DISK', 'public')));

        return $name !== '' ? $name : 'public';
    }

    protected function mediaDisk(): Filesystem
    {
        return Storage::disk($this->mediaDiskName());
    }

    /**
     * Salva o arquivo no disco de mídia e retorna a URL pública.
     */
    protected function storeMediaFile(UploadedFile $file, string $directory): string
    {
        $diskName = $this->mediaDiskName();
        $path = $file->store($directory, $diskName);

        return Storage::disk($diskName)->url($path);
    }

    /**
     * Extrai o caminho relativo (avatars/..., cards/...) de URLs locais (/storage/...) ou Azure Blob.
     */
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

    /**
     * Exclui o arquivo do disco de mídia (com fallback seguro para URLs antigas /storage/ no disco public).
     */
    protected function deleteFileFromStorage(?string $url): void
    {
        $relativePath = $this->extractRelativeStoragePath($url);
        if (!$relativePath) {
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

    private function localPathFromUrl(string $url): string
    {
        $parsed = parse_url($url, PHP_URL_PATH);

        return ($parsed !== false && $parsed !== null) ? $parsed : $url;
    }

    /**
     * Remove host e container de URLs do Azure Blob Storage. Retorna null quando a URL não é do Azure.
     */
    private function stripAzureBase(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === '') {
            return null;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $configured = (string) config('filesystems.disks.azure.url', '');
        if ($configured !== '' && str_starts_with($url, rtrim($configured, '/') . '/')) {
            return substr($url, strlen(rtrim($configured, '/')) + 1);
        }
        if (!str_ends_with($host, '.blob.core.windows.net')) {
            return null;
        }

        $path = ltrim($path, '/');
        $container = trim((string) config('filesystems.disks.azure.container', ''), '/');
        if ($container !== '' && str_starts_with($path, $container . '/')) {
            return substr($path, strlen($container) + 1);
        }

        return str_contains($path, '/') ? substr($path, strpos($path, '/') + 1) : $path;
    }
}
