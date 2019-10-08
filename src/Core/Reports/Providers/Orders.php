<?php

namespace GetCandy\Api\Core\Reports\Providers;

use Carbon\Carbon;

class Orders extends AbstractProvider
{
    public function get()
    {
        $datasets = [];
        $labels = [];

        // Get all orders for the last six months.
        $orders = $this->getOrderQuery()
            ->select(
                \DB::RAW('SUM(order_total) as order_total'),
                \DB::RAW('SUM(delivery_total) as delivery_total'),
                \DB::RAW('SUM(discount_total) as discount_total'),
                \DB::RAW('SUM(sub_total) as sub_total'),
                \DB::RAW('SUM(tax_total) as tax_total'),
                \DB::RAW("DATE_FORMAT(placed_at, '%M %Y') as month")
            )->groupBy(
                \DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')")
            )->get();

        $data = [];
        foreach ($orders as $month) {
            $labels[] = $month->month;
            $data[] = $month->sub_total;
        }

        $dataset = [
            'label'           => 'Order Totals',
            'backgroundColor' => '#E7028C',
            'data'            => $data,
        ];

        return [
            'labels'   => $labels,
            'datasets' => [$dataset],
        ];
    }

    public function metrics()
    {
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
