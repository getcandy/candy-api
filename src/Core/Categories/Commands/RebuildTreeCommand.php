<?php

namespace GetCandy\Api\Core\Categories\Commands;

use Ramsey\Uuid\Uuid;
use Illuminate\Console\Command;
use GetCandy\Api\Core\Search\SearchManager;
use Illuminate\Contracts\Events\Dispatcher;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Search\Actions\IndexDocuments;
use GetCandy\Api\Core\Categories\Actions\RebuildTree;

class RebuildTreeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candy:categories:rebuild';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuilds the category tree';

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
        RebuildTree::run();
    }
}
