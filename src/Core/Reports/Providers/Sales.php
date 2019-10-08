<?php

namespace GetCandy\Api\Core\Reports\Providers;

use DB;
use Carbon\Carbon;

class Sales extends AbstractProvider
{
    public function get()
    {
        $datasets = [];

        $labels = [];
        $ordersData = [];
        $salesData = [];

        $format = '%Y-%m';
        $displayFormat = '%M %Y';

        if ($this->mode == 'weekly') {
            $format = '%Y-%v';
            $displayFormat = 'Week Comm. %d/%m/%Y';
        } elseif ($this->mode == 'daily') {
            $format = '%Y-%m-%d';
            $displayFormat = '%D %M %Y';
        }

        $orders = $this->getOrderQuery()
            ->select(
                DB::RAW('SUM(order_total) as order_total'),
                DB::RAW('COUNT(*) as count'),
                DB::RAW('SUM(delivery_total) as delivery_total'),
                DB::RAW('SUM(discount_total) as discount_total'),
                DB::RAW('SUM(sub_total) as sub_total'),
                DB::RAW('SUM(tax_total) as tax_total'),
                DB::RAW("DATE_FORMAT(placed_at, '{$displayFormat}') as date")
            )->groupBy(
                DB::RAW("DATE_FORMAT(placed_at, '{$format}')")
            )->orderBy('placed_at', 'asc')
            ->get();

        foreach ($orders as $order) {
            $labels[] = $order->date;
            $ordersData[] = $order->count;
            $salesData[] = $order->sub_total;
        }

        $datasets[] = [
            'label'           => 'Orders',
            'backgroundColor' => '#E7028C',
            'yAxisID'         => 'A',
            'borderColor'     => '#E7028C',
            'data'            => $ordersData,
            'fill'            => false,
        ];

        $datasets[] = [
            'label'           => 'Revenue',
            'backgroundColor' => '#0099e5',
            'yAxisID'         => 'B',
            'borderColor'     => '#0099e5',
            'data'            => $salesData,
            'fill'            => false,
        ];

        return [
            'labels'   => $labels,
            'datasets' => $datasets,
        ];
    }

    public function metrics()
    {
        $orders = $this->getOrderQuery(
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()
        )->select('sub_total', 'discount_total')->get();

        // Get orders this month
        $currentMonth = $this->getOrderQuery(
            Carbon::now()->startOfMonth(),
            Carbon::now()
        )->select(
            DB::RAW('sum(sub_total) as sub_total')
        )->first()->sub_total;

        $previousMonth = $this->getOrderQuery(
            Carbon::now()->subMonth()->startOfMonth(),
            Carbon::now()->subMonth()
        )->select(
            DB::RAW('sum(sub_total) as sub_total')
        )->first()->sub_total;

        $currentWeek = $this->getOrderQuery(
            Carbon::now()->startOfWeek(),
            Carbon::now()
        )->select(
            DB::RAW('sum(sub_total) as sub_total')
        )->first()->sub_total;

        $previousWeek = $this->getOrderQuery(
            Carbon::now()->subWeek()->startOfWeek(),
            Carbon::now()->subWeek()
        )->select(
            DB::RAW('sum(sub_total) as sub_total')
        )->first()->sub_total;

        $today = $this->getOrderQuery(
            Carbon::now()->startOfDay(),
            Carbon::now()
        )->select(
            DB::RAW('sum(sub_total) as sub_total')
        )->first()->sub_total;

        $yesterday = $this->getOrderQuery(
            Carbon::now()->subDay()->startOfDay(),
            Carbon::now()->subDay()
        )->select(
            DB::RAW('sum(sub_total) as sub_total')
        )->first()->sub_total;

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
