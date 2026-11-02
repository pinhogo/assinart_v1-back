<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Support\Facades\Storage;

trait InteractsWithMediaStorage
{
    /**
     * Extrai o caminho relativo dentro do disco 'public' a partir de uma URL ou caminho salvo.
     */
    protected function extractRelativeStoragePath(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        $parsedPath = parse_url($url, PHP_URL_PATH);
        $path = ($parsedPath !== false && $parsedPath !== null) ? $parsedPath : $url;

        $path = str_replace('\\', '/', $path);
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return !empty($path) ? $path : null;
    }

    /**
     * Exclui fisicamente um arquivo do disco público caso exista.
     */
    protected function deleteFileFromStorage(?string $url): void
    {
        if (empty($url)) {
            return;
        }

        $relativePath = $this->extractRelativeStoragePath($url);
        if ($relativePath && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }
}
