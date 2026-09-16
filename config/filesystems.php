<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],

        'uploadEditorImg' => [
            'driver' => 'local',
            'root' => public_path('upload/editorImg/'.date('Ymd')), // 这是上传的文件所储存的路径
        ],

        'uploadEditorFile' => [
            'driver' => 'local',
            'root' => public_path('upload/editorFile/'.date('Ymd')), // 这是上传的文件所储存的路径
        ],

        'uploadBannerImg' => [
            'driver' => 'local',
            'root' => public_path('upload/bannerImg/'.date('Ymd')), // 这是上传的文件所储存的路径
        ],

        'uploadCategoryImg' => [
            'driver' => 'local',
            'root' => public_path('upload/categoryImg/'.date('Ymd')), // 这是上传的文件所储存的路径
        ],

        'uploadProductImg' => [
            'driver' => 'local',
            'root' => public_path('upload/productImg/'.date('Ymd')), // 这是上传的文件所储存的路径
        ],

        'uploadLogo' => [
            'driver' => 'local',
            'root' => public_path('upload/logo/'.date('Ymd')), // 这是上传的文件所储存的路径
        ],

        'uploadApplyImg' => [
            'driver' => 'local',
            'root' => public_path('upload/applyImg/'.date('Ymd')), // 这是上传的文件所储存的路径
        ],

        'uploadNewsImg' => [
            'driver' => 'local',
            'root' => public_path('upload/newsImg/'.date('Ymd')), // 这是上传的文件所储存的路径
        ],

        'uploadProductFile' => [
            'driver' => 'local',
            'root' => public_path('upload/productFile/'.date('Ymd')), // 这是上传的文件所储存的路径
        ],

        'uploadQualificationImg' => [
            'driver' => 'local',
            'root' => public_path('upload/qualificationImg/'.date('Ymd')), // 这是上传的文件所储存的路径
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
