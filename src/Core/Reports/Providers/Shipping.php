<?php

namespace GetCandy\Api\Core\Reports\Providers;

use DB;

class Shipping extends AbstractProvider
{
    public function get()
    {
        $format = '%Y-%m';
        $displayFormat = '%M %Y';

        if ($this->mode == 'weekly') {
            $format = '%Y-%v';
            $displayFormat = 'Week Comm. %d/%m/%Y';
        } elseif ($this->mode == 'daily') {
            $format = '%Y-%m-%d';
            $displayFormat = '%D %M %Y';
        }

        $rows = DB::table('order_lines')
        ->select([
            'description',
            DB::RAW("DATE_FORMAT(placed_at, '{$displayFormat}') as date"),
            DB::RAW("DATE_FORMAT(placed_at, '{$format}') as raw_format"),
            DB::RAW('COUNT(*) as count'),
        ])->where(
            'is_shipping', '=', true
        )->join('orders', 'orders.id', '=', 'order_lines.order_id')
        ->whereNotNull('placed_at')
        ->whereBetween('placed_at', [
            $this->from,
            $this->to,
        ])->groupBy(
            DB::RAW("description, DATE_FORMAT(placed_at, '{$format}')")
        )->orderBy('raw_format')->get();

        $labels = [];
        foreach ($rows->groupBy('date') as $month => $shippingMethods) {
            $labels[] = $month;
        }

        $datasets = [];

        $i = 0;

        // dd($rows->groupBy('description'));
        foreach ($rows->groupBy('description') as $key => $shippingMethods) {
            $dataset = [
                'label' => $key,
                'backgroundColor' => $this->colours[$i] ?? $this->colours[0],
                'borderColor' => $this->colours[$i] ?? $this->colours[0],
                'fill' => false,
                'data' => [],
            ];
            foreach ($labels as $label) {
                $data = $shippingMethods->first(function ($method) use ($label) {
                    return $label == $method->date;
                });
                $dataset['data'][] = $data ? $data->count : 0;
            }
            $datasets[] = $dataset;
            $i++;
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }
}
