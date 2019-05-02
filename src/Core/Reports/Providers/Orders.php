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
                $total += $order->order_total;
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
            Carbon::now()->subMonth(),
            Carbon::now()
        )->count();

        $previousMonth = $this->getOrderQuery(
            Carbon::now()->subMonth(2),
            Carbon::now()->subMonth()
        )->count();

        $currentWeek = $this->getOrderQuery(
            Carbon::now()->subWeek(1),
            Carbon::now()
        )->count();

        $previousWeek = $this->getOrderQuery(
            Carbon::now()->subWeek(2),
            Carbon::now()->subWeek(1)
        )->count();

        return [
            'current_month' => $currentMonth,
            'previous_month' => $previousMonth,
            'current_week' => $currentWeek,
            'previous_week' => $previousWeek,
        ];
    }
}
