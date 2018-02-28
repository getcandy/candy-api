<?php

namespace GetCandy\Api\Console\Commands;

use Illuminate\Console\Command;
use GetCandy\Api\Search\SearchContract;
use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Categories\Models\Category;

class ElasticIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candy:elastic:index {--reset=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes a model for Elasticsearch';

    protected $indexables = [
        Product::class,
        Category::class
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

        //TODO: DO this dynamically.
        foreach ($this->indexables as $indexable) {

            $this->info('Indexing ' . $indexable);

            $model = new $indexable;

            if (!$search->indexer()->hasIndexer($model)) {
                $this->error("No Indexer found for {$model}");
            }

            $indexer = $search->indexer()->getIndexer($model);

            if ($this->confirm('Do you want to reset the index?')) {
                $search->indexer()->reset($indexer->getIndexName('en'));
                $search->indexer()->reset($indexer->getIndexName('fr'));
            }

            $items = $model->withoutGlobalScopes()->get();


            $bar = $this->output->createProgressBar($items->count());

            foreach ($items as $model) {
                app(SearchContract::class)->indexer()->indexObject($model);
                $bar->advance();
            }

            $bar->finish();
            $this->info('');
        }

        $this->info('Done!');
    }
}
