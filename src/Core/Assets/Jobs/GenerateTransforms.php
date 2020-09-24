<?php

namespace GetCandy\Api\Core\Assets\Jobs;

use GetCandy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateTransforms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $assets;

    /**
     * @var null|array
     */
    protected $settings;

    /**
     * Create a new job instance.
     *
     * @param  \GetCandy\Api\Core\Assets\Models\Asset[]  $assets
     * @param  null|array  $settings
     * @return void
     */
    public function __construct($assets, $settings = null)
    {
        if (! is_array($assets)) {
            $assets = [$assets];
        }
        $this->assets = collect($assets);
        $this->settings = $settings;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->assets as $asset) {
            GetCandy::assetTransforms()->transform(array_merge(
                ['thumbnail'],
                $this->settings['transforms'] ?? []
            ), $asset);
        }
    }
}
