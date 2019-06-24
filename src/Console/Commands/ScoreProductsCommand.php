<?php

namespace GetCandy\Api\Console\Commands;

use DB;
use Carbon\Carbon;
use Elastica\Document;
use Illuminate\Console\Command;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Products\Models\Product;

class ScoreProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'candy:products:score';

    public function handle(SearchContract $search)
    {
        // Get all products which have been ordered in the past year and the amount of times ordered.
        // $result = DB::table('orders')->whereBetween('placed_at', [
        //     Carbon::now()->subYear(),
        //     Carbon::now()
        // ])->limit(10)->join('order_lines', 'orders.id', '=', 'order_lines.order_id')->get();

        $results = DB::table('order_lines')
        ->select(
            'products.id',
            DB::RAW('COUNT(*) score')
        )
        ->whereBetween('placed_at', [
            Carbon::now()->subYear(),
            Carbon::now(),
        ])
        ->where('is_shipping', '=', false)
        ->join('orders', 'order_lines.order_id', '=', 'orders.id')
        ->join('product_variants', 'order_lines.sku', '=', 'product_variants.sku')
        ->join('products', 'product_variants.product_id', '=', 'products.id')
        ->groupBy('products.id')
        ->get();

        $hasher = \Hashids::connection('product');

        $index = $search->indexer()->against(Product::class)->getCurrentIndex();

        $documents = [];

        foreach ($results as $product) {
            $encodedId = $hasher->encode($product->id);

            $document = new Document($encodedId);
            $document->set('popularity', $product->score);
            $document->setType('products');
            $documents[] = $document;
        }

        $index->indexObjects($documents);
    }
}
