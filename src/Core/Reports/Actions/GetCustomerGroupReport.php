<?php

namespace GetCandy\Api\Core\Reports\Actions;

use Carbon\CarbonPeriod;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroups;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Facades\DB;

class GetCustomerGroupReport extends AbstractAction
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
            'to' => 'nullable|date',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        $query = Order::whereNotNull('placed_at');

        // $displayFormat = $formats['display'];
        // $queryFormat = $formats['format'];

        // Get our customer groups.
        $groups = FetchCustomerGroups::run([
            'paginate' => false,
        ]);

        $period = CarbonPeriod::create($this->from, '1 month', $this->to);

        $report = $groups->mapWithKeys(function ($group) use ($period) {
            $guestOrders = null;

            if ($group->default) {
                $guestOrders = $this->getGuestOrdersResult();
            }

            $result = Order::whereNotNull('placed_at')
                ->whereBetween('placed_at', [
                    $this->from,
                    $this->to,
                ])->join('users', 'users.id', '=', 'orders.user_id')
                ->join('customers', 'customers.id', '=', 'users.customer_id')
                ->join('customer_customer_group', function ($join) use ($group) {
                    $join->on('customer_customer_group.customer_id', '=', 'customers.id')
                        ->where('customer_customer_group.customer_group_id', '=', $group->id);
                })
                ->select($this->getSelectStatement())->groupBy(
                    DB::RAW("DATE_FORMAT(placed_at, '%Y%m')"),
                    'customer_customer_group.customer_group_id'
                )->orderBy($this->getOrderByClause(), 'desc')->get()->map(function ($row) use ($guestOrders) {
                    if (! $guestOrders) {
                        return $row;
                    }
                    // Find the guest orders that match our monthstamp
                    $match = $guestOrders->first(function ($guestRow) use ($row) {
                        return $guestRow->monthstamp == $row->monthstamp;
                    });

                    if (! $match) {
                        return $row;
                    }

                    $row->order_total += $match->order_total;
                    $row->delivery_total += $match->delivery_total;
                    $row->discount_total += $match->discount_total;
                    $row->sub_total += $match->sub_total;
                    $row->tax_total += $match->tax_total;
                    $row->order_count += $match->order_count;

                    return $row;
                });

            $dates = collect();

            foreach ($period as $date) {
                $report = $result->first(function ($month) use ($date) {
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
                $dates->push($report);
            }

            return [$group->handle => [
                'label' => $group->name,
                'handle' => $group->handle,
                'default' => (bool) $group->default,
                'data' => $dates,
            ]];
        });

        return [
            'period' => collect($period->toArray())->map(function ($date) {
                return [
                    'label' => $date->format('F Y'),
                    'date' => $date,
                ];
            }),
            'data' => $report,
        ];
    }

    protected function getSelectStatement()
    {
        return [
            DB::RAW('COUNT(*) as order_count'),
            DB::RAW('SUM(order_total) as order_total'),
            DB::RAW('SUM(delivery_total) as delivery_total'),
            DB::RAW('SUM(discount_total) as discount_total'),
            DB::RAW('SUM(sub_total) as sub_total'),
            DB::RAW('SUM(tax_total) as tax_total'),
            DB::RAW("DATE_FORMAT(placed_at, '%M') as month"),
            DB::RAW("DATE_FORMAT(placed_at, '%Y') as year"),
            DB::RAW("DATE_FORMAT(placed_at, '%Y%m') as monthstamp"),
        ];
    }

    protected function getGroupByClause()
    {
        return DB::RAW("DATE_FORMAT(placed_at, '%Y%m')");
    }

    protected function getOrderByClause()
    {
        return DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')");
    }

    protected function getGuestOrdersResult()
    {
        return Order::whereNotNull('placed_at')
        ->whereBetween('placed_at', [
            $this->from,
            $this->to,
        ])->where('sub_total', '>', 0)->whereNull('user_id')->select(
            $this->getSelectStatement()
        )->groupBy(
            $this->getGroupByClause()
        )->orderBy($this->getOrderByClause(), 'desc')->get();
    }
}
