<?php

namespace GetCandy\Api\Installer\Runners;

use DB;
use GetCandy\Api\Installer\Contracts\InstallRunnerContract;

class AssetRunner extends AbstractRunner implements InstallRunnerContract
{
    public function run()
    {
        // Are asset sources already installed?
        if (! DB::table('asset_sources')->count()) {
            $this->installSources();
        }
        if (! DB::table('transforms')->count()) {
            $this->installTransforms();
        }
    }

    protected function installTransforms()
    {
        DB::table('transforms')->insert([
            [
                'name' => 'Thumbnail',
                'handle' => 'thumbnail',
                'mode' => 'fit',
                'position' => 'center-center',
                'width' => 500,
                'height' => 500,
            ],
            [
                'name' => 'Large Thumbnail',
                'handle' => 'large_thumbnail',
                'mode' => 'fit',
                'position' => 'center-center',
                'width' => 800,
                'height' => 800,
            ],
        ]);
    }

    /**
     * Install asset sources.
     *
     * @return void
     */
    protected function installSources()
    {
        DB::table('asset_sources')->insert([
            [
                'name' => 'Product images',
                'handle' => 'products',
                'disk' => 'public',
                'path' => 'products',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Category images',
                'handle' => 'categories',
                'disk' => 'public',
                'path' => 'categories',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Channel images',
                'handle' => 'channels',
                'disk' => 'public',
                'path' => 'channels',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
