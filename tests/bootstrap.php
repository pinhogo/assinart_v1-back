<?php

require __DIR__ . '/../vendor/autoload.php';

if (!function_exists('mb_split')) {
    function mb_split(string $pattern, string $string, int $limit = -1): array|false
    {
        return preg_split('/' . $pattern . '/u', $string, $limit);
    }
}

if (!class_exists('finfo')) {
    if (!defined('FILEINFO_NONE')) {
        define('FILEINFO_NONE', 0);
    }
    if (!defined('FILEINFO_MIME_TYPE')) {
        define('FILEINFO_MIME_TYPE', 16);
    }
    if (!defined('FILEINFO_MIME')) {
        define('FILEINFO_MIME', 1040);
    }

    class finfo
    {
        public function __construct(int $flags = 0, ?string $magic_file = null)
        {
        }

        public function buffer(string $string, int $flags = 0, $context = null): string|false
        {
            return false;
        }

        public function file(string $filename, int $flags = 0, $context = null): string|false
        {
            return false;
        }
    }
}
