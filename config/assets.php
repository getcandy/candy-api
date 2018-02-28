<?php

return [
    'max_filesize' => env('ASSETS_MAX_FILESIZE', 2000),
    'allowed_filetypes' => env('ASSETS_ALLOWED_EXTENSIONS', 'jpg,jpeg,png,pdf,gif,bmp,svg,doc,docx,xls,csv'),
    'upload_drivers' => [
        'vimeo' => GetCandy\Api\Assets\Drivers\Vimeo::class,
        'application' => GetCandy\Api\Assets\Drivers\File::class,
        'youtube' => GetCandy\Api\Assets\Drivers\YouTube::class,
        'image' => GetCandy\Api\Assets\Drivers\Image::class,
        'external' => GetCandy\Api\Assets\Drivers\ExternalImage::class
    ]
];
