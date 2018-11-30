<?php

namespace GetCandy\Api\Core\Orders\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use GetCandy\Api\Core\Orders\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GetCandy\Api\Core\Products\Models\ProductVariant;

class ProcessRecommendedProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    /**
     * Create a new job instance.
     *
     * @param  Podcast  $podcast
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @param  AudioProcessor  $processor
     * @return void
     *
     * select ol2.sku, count(ol2.sku)
     * from orders as o
     * inner join order_lines as ol1 on ol1.`order_id` = o.id
     * inner join order_lines as ol2 on (ol2.`order_id` = o.id and ol2.id != ol1.id)
     * where ol1.`sku` = '4999610'
     *
     * group by ol2.sku
     * order by count(ol2.sku) desc
     *
     * limit 10
     */
    public function handle()
    {
        // For each order line, get the top products.

        // Remove any manual or shipping.
        $lines = $this->order->lines->filter(function ($line) {
            return ! $line->is_manual;
        })->filter(function ($line) {
            return ! $line->is_shipping;
        });

        if ($lines->count() <= 1) {
            return;
        }

        /*
         * * select ol2.sku, count(ol2.sku)
            * from orders as o
            * inner join order_lines as ol1 on ol1.`order_id` = o.id
            * inner join order_lines as ol2 on (ol2.`order_id` = o.id and ol2.id != ol1.id)
            * where ol1.`sku` = '4999610'

            * group by ol2.sku
            * order by count(ol2.sku) desc
         */
        foreach ($this->order->lines as $line) {
            $sku = $line->sku;

            $topProducts = DB::table('orders')->select(
                'ol2.sku',
                'pv2.product_id as related_product_id',
                DB::RAW('count(ol2.sku) as count')
            )->join('order_lines as ol1', 'ol1.order_id', '=', 'orders.id')
            ->join('order_lines as ol2', function ($join) {
                $join->on('ol2.order_id', '=', 'orders.id')
                    ->on('ol2.id', '!=', 'ol1.id');
            })->join('product_variants as pv2', 'ol2.sku', '=', 'pv2.sku')
                ->groupBy('ol2.sku')
                ->limit(5)
                ->orderBy(
                    DB::raw("count('ol2.sku')"),
                    'desc'
                )
            ->where('ol1.sku', '=', $sku)
            ->get();

            $variant = ProductVariant::where('sku', '=', $sku)->first();

            if (! $variant) {
                continue;
            }

            DB::table('product_recommendations')->where('product_id', '=', $variant->product_id)->delete();

            $payload = $topProducts->map(function ($item) use ($variant) {
                return [
                    'product_id' => $variant->product_id,
                    'related_product_id' => $item->related_product_id,
                    'count' => $item->count,
                ];
            });

            DB::table('product_recommendations')->insert($payload->toArray());
        }
    }
}
