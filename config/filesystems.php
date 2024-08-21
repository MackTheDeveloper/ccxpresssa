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
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
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

        /* 's3' => [
            'driver' => 's3',
            'key' => 'AKIAYRVCP4JK7ZY377GV',
            'secret' => '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',
            'region' => 'us-east-1',
            'bucket' => 'cargo-live-site',
        ], */
        
        // 's3' => [
        //     'driver' => 's3',
        //     'key' => 'AKIAYRVCP4JKZPIAAYAH',
        //     'secret' => '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',
        //     'region' => 'us-east-1',
        //     'bucket' => 'cargo-live-site',
        // ],

        // 's3' => [
        //     'driver' => 's3',
        //     'key' => 'AKIAYRVCP4JK7KSUXUYV',
        //     'secret' => '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',
        //     'region' => 'us-east-1',
        //     'bucket' => 'cargo-live-site',
        // ],
        // 4th June 2024 changed s3 key
        's3' => [
            'driver' => 's3',
            'key' => 'AKIAYRVCP4JKURMUMVEF',
            'secret' => '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',
            'region' => 'us-east-1',
            'bucket' => 'cargo-live-site',
        ],

    ],

];
