<?php

namespace GetCandy\Api\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SyncAttributeDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ids;

    protected $type;

    /**
     * Create a new job instance.
     *
     * @param array $ids
     * @param string $type
     *
     * @return void
     */
    public function __construct(array $ids, $type = null)
    {
        $this->ids = $ids;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $attributes = app('api')->attributes()->getAttributables($this->ids, $this->type);
        foreach ($attributes as $attribute) {
            foreach ($attribute->attributables as $record) {
                $model = $record->attributable;
                $data = $model->attribute_data;
                if (empty($model->attribute_data[$attribute->handle])) {
                    $data[$attribute->handle] = $model->getDataMapping();
                    $model->attribute_data = $data;
                    $model->save();
                }
            }
        }
    }
}
