<?php

namespace GetCandy\Api\Assets\Jobs;

use GetCandy\Api\Assets\Models\Asset;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GenerateTransforms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \GetCandy\Api\Assets\Models\Asset
     */
    protected $assets;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($assets)
    {
        if (!is_array($assets)) {
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
            $settings = $asset->assetable->settings;
            app('api')->transforms()->transform(array_merge(
                ['thumbnail'],
                $settings['transforms']
            ), $asset);
        }
    }
}
