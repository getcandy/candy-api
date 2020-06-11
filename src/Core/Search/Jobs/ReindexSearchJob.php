<?php

namespace GetCandy\Api\Core\Search\Jobs;

use GetCandy\Api\Core\Search\SearchContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReindexSearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $model;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     *
     * @param  \GetCandy\Api\Core\Search\SearchContract  $search
     * @return void
     */
    public function handle(SearchContract $search)
    {
        $search->indexer()->reindex(new $this->model);
    }
}
