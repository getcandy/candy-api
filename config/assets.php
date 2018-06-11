<?php

return [
    'max_filesize' => env('ASSETS_MAX_FILESIZE', 2000),
    'allowed_filetypes' => env('ASSETS_ALLOWED_EXTENSIONS', 'jpg,jpeg,png,pdf,gif,bmp,svg,doc,docx,xls,csv'),
    'upload_drivers' => [
        'vimeo' => GetCandy\Api\Core\Assets\Drivers\Vimeo::class,
        'application' => GetCandy\Api\Core\Assets\Drivers\File::class,
        'youtube' => GetCandy\Api\Core\Assets\Drivers\YouTube::class,
        'image' => GetCandy\Api\Core\Assets\Drivers\Image::class,
        'external' => GetCandy\Api\Core\Assets\Drivers\ExternalImage::class,
    ],
];
