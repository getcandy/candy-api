<?php

namespace GetCandy\Api\Jobs;

use GetCandy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAttributeDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * An array of attributes hashed ids.
     *
     * @var array
     */
    protected $ids;

    /**
     * @var string|null
     */
    protected $type;

    /**
     * Create a new job instance.
     *
     * @param  array  $ids
     * @param  string|null  $type
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
        $attributes = GetCandy::attributes()->getAttributables($this->ids, $this->type);
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
