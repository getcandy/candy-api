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
        $orders = $this->getOrderQuery()->get();

        $months = $orders->groupBy(function ($item) {
            return Carbon::parse($item->placed_at)->format('F Y');
        });

        $data = [];
        foreach ($months as $month => $orders) {
            $labels[] = $month;

            $total = 0;

            foreach ($orders as $order) {
                $total += $order->sub_total + $order->delivery_total - $order->discount_total;
            }

            $data[] = $total;
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
            'current_month' => $currentMonth,
            'previous_month' => $previousMonth,
            'current_week' => $currentWeek,
            'previous_week' => $previousWeek,
            'today' => $today,
            'yesterday' => $yesterday,
        ];
    }
}
