<?php

namespace GetCandy\Api\Core\Assets\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateTransforms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \GetCandy\Api\Core\Assets\Models\Asset
     */
    protected $assets;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($assets)
    {
        if (! is_array($assets)) {
            $assets = [$assets];
        }
        $this->assets = collect($assets);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->assets as $asset) {

            // Do it this way to avoid global scope issues
            $settings = (new $asset->assetable_type)->settings;

            app('api')->transforms()->transform(array_merge(
                ['thumbnail'],
                $settings['transforms']
            ), $asset);
        }
    }
}
