<?php

namespace GetCandy\Api\Console\Commands;

use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Search\Jobs\ReindexSearchJob;
use GetCandy\Api\Core\Search\SearchContract;
use Illuminate\Console\Command;

class CandySearchIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candy:search:index {--queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindexes the search';

    /**
     * The models that should be indexed.
     *
     * @var array
     */
    protected $indexables = [
        Product::class,
        Category::class,
    ];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $search = app(SearchContract::class);

        foreach ($this->indexables as $indexable) {
            $this->info('Indexing '.$indexable);
            $model = new $indexable;
            if ($this->option('queue')) {
                ReindexSearchJob::dispatch($indexable);
            } else {
                $search->indexer()->reindex($model, config('getcandy.search.batch_size', 1000));
            }
        }

        $this->info('Done!');
    }
}
