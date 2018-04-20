<?php

namespace GetCandy\Api\Core\Assets\Jobs;

use Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CleanUpAssetFiles implements ShouldQueue
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
            $settings = $asset->source;

            $disk = Storage::disk($asset->source->disk);

            $assets = [];

            $assets[] = $asset->location.'/'.$asset->filename;

            foreach ($asset->transforms as $transform) {
                $assets[] = $transform->location.'/'.$transform->filename;
            }

            $disk->delete($assets);
        }
    }
}
