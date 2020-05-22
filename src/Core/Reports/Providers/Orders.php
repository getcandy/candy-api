<?php

namespace GetCandy\Api\Core\Reports\Providers;

use Carbon\Carbon;
use DB;

class Orders extends AbstractProvider
{
    public function get()
    {
        $datasets = [];
        $labels = [];

        // Get all orders for the last six months.
        $orders = $this->getOrderQuery()
            ->select(
                DB::RAW('SUM(order_total) as order_total'),
                DB::RAW('SUM(delivery_total) as delivery_total'),
                DB::RAW('SUM(discount_total) as discount_total'),
                DB::RAW('SUM(sub_total) as sub_total'),
                DB::RAW('SUM(tax_total) as tax_total'),
                DB::RAW("DATE_FORMAT(placed_at, '%M %Y') as month")
            )->groupBy(
                DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')")
            )->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'desc')->get();

        return $orders->map(function ($order) {
            return [
                'month' => $order->month,
                'sub_total' => $order->sub_total,
                'delivery_total' => $order->delivery_total,
                'tax_total' => $order->tax_total,
                'order_total' => $order->order_total,
                'discount_total' => $order->discount_total,
            ];
        });
    }

    public function customers()
    {
        $orders = DB::table('orders')->whereNotNull('placed_at')
        ->whereBetween('placed_at', [
            $this->from,
            $this->to,
        ])->select(
            DB::RAW("DATE_FORMAT(placed_at, '%Y%m') as month"),
            DB::RAW('case when billing_email is null then users.email else billing_email end as orderemail'),
            DB::RAW('(select count(*) from orders join users on users.id = orders.user_id where users.email = orderemail) as account_orders'),
            DB::RAW('(select count(*) from orders where billing_email = orderemail) as guest_orders')
        )->leftJoin('users', 'users.id', '=', 'orders.user_id');

        $orders = $orders->get()->groupBy('month');

        return $orders->mapWithKeys(function ($orders, $month) {
            $new = 0;
            $returning = 0;

            $groupedOrders = $orders->groupBy('orderemail');

            foreach ($groupedOrders as $orders) {
                if ($orders->sum('account_orders', 'guest_orders') == 1) {
                    $new++;
                } else {
                    $returning++;
                }
            }

            $date = \Carbon\Carbon::createFromFormat('Ym', $month);

            return [$month => [
                'label' => $date->format('F Y'),
                'new' => $new,
                'returning' => $returning,
                'total' => $returning + $new,
            ]];
        })->sortKeysDesc();
    }

    public function averages()
    {
        $formats = $this->getDateFormat();
        $displayFormat = $formats['display'];
        $queryFormat = $formats['format'];
        $orders = $this->getOrderQuery()
            ->select(
                DB::RAW('ROUND(AVG(order_total), 0) as order_total'),
                DB::RAW('ROUND(AVG(delivery_total), 0) as delivery_total'),
                DB::RAW('ROUND(AVG(discount_total), 0) as discount_total'),
                DB::RAW('ROUND(AVG(sub_total), 0) as sub_total'),
                DB::RAW('ROUND(AVG(tax_total), 0) as tax_total'),
                DB::RAW("DATE_FORMAT(placed_at, '{$displayFormat}') as date")
            )->groupBy(
                DB::RAW("DATE_FORMAT(placed_at, '{$queryFormat}')")
            )->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'desc')->get();

        return $orders->map(function ($order) {
            return [
                'date' => $order->date,
                'sub_total' => $order->sub_total,
                'delivery_total' => $order->delivery_total,
                'tax_total' => $order->tax_total,
                'order_total' => $order->order_total,
                'discount_total' => $order->discount_total,
            ];
        });
    }

    public function bestSellers($limit = 50)
    {
        $stats = DB::table('order_lines')
            ->select(
                DB::RAW('COUNT(*) as product_count'),
                'description',
                'sku',
                DB::RAW("DATE_FORMAT(placed_at, '%Y-%m-01') as month")
            )
            ->join('orders', 'orders.id', '=', 'order_lines.order_id')
            ->whereIsManual(0)
            ->whereIsShipping(0)
            ->whereNotNull('placed_at')
            ->whereBetween('placed_at', [
                $this->from,
                $this->to,
            ])->orderBy(
                DB::RAW('COUNT(*)'), 'desc'
            )->groupBy(
                'sku',
                DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')")
            );

        $result = $stats->get()
            ->groupBy('month')
            ->sortKeysDesc()
            ->mapWithKeys(function ($rows, $month) {
                return [
                    Carbon::createFromFormat('Y-m-d', $month)->toIsoString() => [
                        'products' => $rows->slice(0, 10),
                    ],
                ];
            });

        // $products = collect();

        // /**
        //  *  [
        //  *   product => 'Screw',
        //  *   sku => 1231234
        //  *   months => []
        //  * ]
        //  *
        //  */
        // foreach ($result as $month) {
        //    foreach ($month['products'] as $product) {
        //        if (empty($products[$product->sku])) {
        //            $products[$product->sku] = [
        //                'product' => $product->description,
        //                'sku' => $product->sku,
        //                'months' => collect(),
        //            ];
        //        }
        //        $products[$product->sku]['months']->push([
        //            'month' => Carbon::createFromFormat('Y-m-d', $product->month)->toIsoString(),
        //            'count' => $product->product_count,
        //        ]);
        //    }
        // }

        return $result;
    }

    public function metrics()
    {
        Carbon::useMonthsOverflow(false);

        // Get orders this month
        $currentMonth = $this->getOrderQuery(
            Carbon::now()->startOfMonth(),
            Carbon::now()
        )->count();

        $previousMonth = $this->getOrderQuery(
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()
        )->count();

        $currentWeek = $this->getOrderQuery(
            Carbon::now()->startOfWeek(),
            Carbon::now()
        )->count();

        $previousWeek = $this->getOrderQuery(
            Carbon::now()->subWeek()->startOfWeek(),
            Carbon::now()->subWeek()
        )->count();

        $today = $this->getOrderQuery(
            Carbon::now()->startOfDay(),
            Carbon::now()
        )->count();

        $yesterday = $this->getOrderQuery(
            Carbon::now()->subDay()->startOfDay(),
            Carbon::now()->subDay()
        )->count();

        return [
            'current_month' => $currentMonth ?? 0,
            'previous_month' => $previousMonth ?? 0,
            'today' => $today ?? 0,
            'yesterday' => $yesterday ?? 0,
            'current_week' => $currentWeek ?? 0,
            'previous_week' => $previousWeek ?? 0,
        ];
    }
}
