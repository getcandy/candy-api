<?php

namespace GetCandy\Api\Core\Reports\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Facades\DB;

class GetProductBestSellers extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('view-reports');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'from' => 'nullable|date',
            'to' => 'nullable|date|after:from',
            'term' => 'nullable|string',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        $query = DB::table('order_lines')
            ->select(
                DB::RAW('SUM(quantity) as quantity'),
                DB::RAW('SUM(line_total) as sub_total'),
                'description',
                'sku',
                DB::RAW("DATE_FORMAT(placed_at, '%Y-%m-01') as month")
            )
            ->join('orders', 'orders.id', '=', 'order_lines.order_id')
            ->whereNotNull('placed_at')
            ->whereBetween('placed_at', [
                $this->from,
                $this->to,
            ])->whereIsManual(0)
            ->whereIsShipping(0)
            ->groupBy('sku')
            ->orderBy(
                DB::RAW('SUM(quantity)'), 'desc'
            );

        if ($this->term) {
            $query->where('sku', 'LIKE', "%{$this->term}%");
        }

        return $query->paginate(50);
    }
}
