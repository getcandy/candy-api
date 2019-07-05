<?php

namespace GetCandy\Api\Jobs\Utils;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GetCandy\Api\Core\Utils\Import\Models\Import;
use GetCandy\Api\Core\Utils\Import\ImportManagerContract;

class ImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $import;

    /**
     * Create a new job instance.
     *
     * @param array $ids
     * @param string $type
     *
     * @return void
     */
    public function __construct(Import $import)
    {
        $this->import = $import;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ImportManagerContract $importer)
    {
        $this->import->update([
            'started_at' => Carbon::now(),
        ]);

        // Get our driver instance.
        $driver = $importer->with(
            $this->import->type
        )->using($this->import)
        ->handle();
    }
}
