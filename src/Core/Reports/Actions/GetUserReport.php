<?php

namespace GetCandy\Api\Core\Reports\Actions;

use Carbon\CarbonPeriod;
use GetCandy;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GetUserReport extends AbstractAction
{
    protected $user;

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
            'from' => 'date|before:to',
            'to' => 'date|after:from',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle($userId)
    {
        $this->set('from', Carbon::parse($this->from));
        $this->set('to', Carbon::parse($this->to));
        $userModel = GetCandy::getUserModel();

        $userId = (new $userModel())->decodeId($userId);

        $this->user = (new $userModel())->find($userId);
        $period = CarbonPeriod::create($this->from, '1 month', $this->to);

        // Top products ordered over time period
        $productLines = $this->getProductOrderLines();
        $userSpending = $this->getUserSpending();

        return [
            'period' => collect($period->toArray())->map(function ($date) {
                return [
                    'label' => $date->format('F Y'),
                    'date' => $date,
                ];
            }),
            'metrics' => ['data' => $this->getMetrics()],
            'purchasing_report' => ['data' => $productLines],
            'spending' => ['data' => $userSpending],
        ];
        // Order totals over time period.
    }

    protected function getMetrics()
    {
        $result = DB::table('orders')
        ->select(
            DB::RAW('COUNT(*) as order_count'),
            DB::RAW('SUM(order_total) as order_total'),
            DB::RAW('SUM(delivery_total) as delivery_total'),
            DB::RAW('SUM(discount_total) as discount_total'),
            DB::RAW('SUM(sub_total) as sub_total'),
            DB::RAW('SUM(tax_total) as tax_total'),
        )
        ->whereNotNull('placed_at')
        ->groupBy('billing_email')
        ->where('billing_email', '=', $this->user->email)
        ->first();

        if (! $result) {
            return (object) [
                'order_total' => 0,
                'discount_total' => 0,
                'order_count' => 0,
                'sub_total' => 0,
            ];
        }

        return $result;
    }

    protected function getUserSpendingQuery($from, $to)
    {
        return DB::table('orders')
        ->select(
            DB::RAW('COUNT(*) as order_count'),
            DB::RAW('SUM(order_total) as order_total'),
            DB::RAW('SUM(delivery_total) as delivery_total'),
            DB::RAW('SUM(discount_total) as discount_total'),
            DB::RAW('SUM(sub_total) as sub_total'),
            DB::RAW('SUM(tax_total) as tax_total'),
            DB::RAW("DATE_FORMAT(placed_at, '%M') as month"),
            DB::RAW("DATE_FORMAT(placed_at, '%Y') as year"),
            DB::RAW("DATE_FORMAT(placed_at, '%Y%m') as monthstamp")
        )
        ->whereNotNull('placed_at')
        ->whereBetween('placed_at', [
            $from,
            $to,
        ])->groupBy(DB::RAW("DATE_FORMAT(placed_at, '%Y%m')"))
        ->where('billing_email', '=', $this->user->email);
    }

    protected function getUserSpending()
    {
        $currentPeriodRows = $this->getUserSpendingQuery($this->from, $this->to)->get();
        $currentPeriod = collect();

        $period = CarbonPeriod::create($this->from, '1 month', $this->to);

        foreach ($period as $date) {
            $report = $currentPeriodRows->first(function ($month) use ($date) {
                return $month->monthstamp == $date->format('Ym');
            });
            if (! $report) {
                $report = (object) [
                    'order_total' => 0,
                    'delivery_total' => 0,
                    'discount_total' => 0,
                    'sub_total' => 0,
                    'month' => $date->format('F'),
                    'year' => $date->format('Y'),
                    'tax_total' => 0,
                ];
            }
            $currentPeriod->push($report);
        }

        $previousPeriodRows = $this->getUserSpendingQuery($this->from->subYear(), $this->to->subYear())->get();
        $previousPeriod = collect();
        $period = CarbonPeriod::create($this->from, '1 month', $this->to);

        foreach ($period as $date) {
            $report = $previousPeriodRows->first(function ($month) use ($date) {
                return $month->monthstamp == $date->format('Ym');
            });
            if (! $report) {
                $report = (object) [
                    'order_total' => 0,
                    'delivery_total' => 0,
                    'discount_total' => 0,
                    'sub_total' => 0,
                    'month' => $date->format('F'),
                    'year' => $date->format('Y'),
                    'tax_total' => 0,
                ];
            }
            $previousPeriod->push($report);
        }

        return [
            'current_period' => ['data' => $currentPeriod],
            'previous_period' => ['data' => $previousPeriod],
        ];
    }

    protected function getProductOrderLines()
    {
        return DB::table('order_lines')
        ->select(
            DB::RAW('COUNT(*) as order_count'),
            DB::RAW('SUM(quantity) as quantity'),
            DB::RAW('SUM(line_total) as sub_total'),
            'description',
            'order_lines.sku',
            DB::RAW("MAX(DATE_FORMAT(placed_at, '%Y-%m-%d')) as last_ordered")
        )
        ->join('orders', 'orders.id', '=', 'order_lines.order_id')
        ->leftJoin('product_variants', 'product_variants.sku', '=', 'order_lines.sku')
        ->leftJoin('products', 'products.id', '=', 'product_variants.product_id')
        ->whereNotNull('placed_at')
        ->whereBetween('placed_at', [
            $this->from,
            $this->to,
        ])->whereIsManual(0)
        ->whereIsShipping(0)
        ->where('billing_email', '=', $this->user->email)
        ->groupBy('order_lines.sku')
        ->orderBy(DB::RAW('sub_total'), 'desc')->get();
    }
}
