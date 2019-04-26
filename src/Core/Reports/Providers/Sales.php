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

        $orderQuery = $this->getOrderQuery();

        $format = 'Y-m';
        $displayFormat = 'M Y';

        if ($this->mode == 'weekly') {
            $format = 'Y-W';
        } elseif ($this->mode == 'daily') {
            $format = 'Y-m-d';
            $displayFormat = 'dS M Y';
        }
        // Now we should group by year/month
        $times = $orderQuery->orderBy('placed_at')->get()->groupBy(function ($ord) use ($format) {
            return $ord->placed_at->format($format);
        });

        foreach ($times as $time => $orders) {
            // Carbon doesn't like parsing weekly dates so need to fudge a bit.
            if ($this->mode == 'weekly') {
                $fragments = explode('-', $time);
                $date = Carbon::now()->setISODate($fragments[0], $fragments[1]);
                $from = Carbon::now()->setISODate($fragments[0], $fragments[1])->startOfWeek();
                $to = Carbon::now()->setISODate($fragments[0], $fragments[1])->endOfWeek();

                $label = $from->format('dS') . '/' . $to->format('dS') . ' ' . $date->format('M Y');
            } else {
                $label = Carbon::createFromFormat($format, $time)->format($displayFormat);
            }
            $labels[] = $label;

            $ordersData[] = $orders->count();
            $salesData[] = $orders->sum('order_total');
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
            'label'           => 'Sales',
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
        // Get orders this month
        $currentMonth = $this->getOrderQuery(
            Carbon::now()->subMonth(),
            Carbon::now()
        )->sum('order_total');

        $previousMonth = $this->getOrderQuery(
            Carbon::now()->subMonth(2),
            Carbon::now()->subMonth()
        )->sum('order_total');

        $currentWeek = $this->getOrderQuery(
            Carbon::now()->subWeek(1),
            Carbon::now()
        )->sum('order_total');

        $previousWeek = $this->getOrderQuery(
            Carbon::now()->subWeek(2),
            Carbon::now()->subWeek()
        )->sum('order_total');

        return [
            'current_month' => $currentMonth,
            'previous_month' => $previousMonth,
            'current_week' => $currentWeek,
            'previous_week' => $previousWeek,
        ];
    }
}
