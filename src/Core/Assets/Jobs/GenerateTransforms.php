<?php

namespace GetCandy\Api\Core\Assets\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateTransforms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var \GetCandy\Api\Core\Assets\Models\Asset
     */
    protected $assets;

    protected $settings;

    /**
     * Create a new job instance.
     *
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

            app('api')->transforms()->transform(array_merge(
                ['thumbnail'],
                $this->settings['transforms'] ?? []
            ), $asset);
        }
    }
}
