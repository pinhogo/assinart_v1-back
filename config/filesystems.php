<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for storing files.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    'media_disk' => env('MEDIA_DISK', env('FILESYSTEM_DISK', 'public')),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'azure' => [
            'driver' => 'azure-storage-blob',
            'connection_string' => env('AZURE_STORAGE_CONNECTION_STRING', sprintf(
                'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s;EndpointSuffix=%s',
                (string) env('AZURE_STORAGE_NAME', env('AZURE_STORAGE_ACCOUNT', '')),
                (string) env('AZURE_STORAGE_KEY', ''),
                (string) env('AZURE_STORAGE_ENDPOINT_SUFFIX', 'core.windows.net'),
            )),
            'container' => env('AZURE_STORAGE_CONTAINER', 'media'),
            'url' => env('AZURE_STORAGE_URL'),
            'endpoint' => env('AZURE_STORAGE_ENDPOINT'),
            'prefix' => env('AZURE_STORAGE_PREFIX', ''),
            'visibility' => 'public',
            'throw' => false,
        ],

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL', 'http://localhost:8000').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
