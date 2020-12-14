<?php

namespace GetCandy\Api\Core\Search\Commands;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Search\Actions\IndexDocuments;
use GetCandy\Api\Core\Search\SearchManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Events\Dispatcher;
use Ramsey\Uuid\Uuid;

class IndexProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candy:products:reindex {batchsize=1000} {--queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindexes products';

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
     * @return void
     */
    public function handle(Dispatcher $events, SearchManager $manager)
    {
        $batchsize = (int) $this->argument('batchsize');
        $total = Product::withoutGlobalScopes()->count();

        $this->output->text('Indexing '.$total.' products in '.ceil($total / $batchsize).' batches');

        $batches = ceil($total / $batchsize);
        $bar = $this->output->createProgressBar($batches);

        $uuid = Uuid::uuid4()->toString();

        Product::withoutGlobalScopes()->with([
            'attributes',
            'customerGroups',
            'channels',
            'variants.customerPricing',
            'categories',
        ])->chunk($batchsize, function ($products, $index) use ($manager, $uuid, $batches, $bar) {
            IndexDocuments::run([
                'driver' => $manager->with(
                    config('getcandy.search.driver')
                ),
                'documents' => $products,
                'uuid' => $uuid,
                'final' => (int) $batches === $index,
            ]);
            $bar->advance();
        });
    }
}
