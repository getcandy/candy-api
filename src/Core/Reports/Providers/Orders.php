<?php

namespace GetCandy\Api\Core\Reports\Providers;

use DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroups;

class Orders extends AbstractProvider
{
    public function get()
    {
        $datasets = [];
        $labels = [];

        // Get all orders for the last six months.
        $results = $this->getOrderQuery($this->from, $this->to)
            ->select(
                DB::RAW('SUM(order_total) as order_total'),
                DB::RAW('SUM(delivery_total) as delivery_total'),
                DB::RAW('SUM(discount_total) as discount_total'),
                DB::RAW('SUM(sub_total) as sub_total'),
                DB::RAW('SUM(tax_total) as tax_total'),
                DB::RAW("DATE_FORMAT(placed_at, '%M') as month"),
                DB::RAW("DATE_FORMAT(placed_at, '%Y') as year"),
                DB::RAW("DATE_FORMAT(placed_at, '%Y%m') as monthstamp")
            )->groupBy(
                DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')")
            )->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'desc')->get();

        $currentPeriod = collect();
        $period = CarbonPeriod::create($this->from, '1 month', $this->to);
        foreach ($period as $date) {
            // Find our records for this period.
            $report = $results->first(function ($month) use ($date) {
                return $month->monthstamp == $date->format('Ym');
            });
            if (!$report) {
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

        $results = $this->getOrderQuery($this->from->subYear(), $this->to->subYear())
            ->select(
                DB::RAW('SUM(order_total) as order_total'),
                DB::RAW('SUM(delivery_total) as delivery_total'),
                DB::RAW('SUM(discount_total) as discount_total'),
                DB::RAW('SUM(sub_total) as sub_total'),
                DB::RAW('SUM(tax_total) as tax_total'),
                DB::RAW("DATE_FORMAT(placed_at, '%M') as month"),
                DB::RAW("DATE_FORMAT(placed_at, '%Y') as year"),
                DB::RAW("DATE_FORMAT(placed_at, '%Y%m') as monthstamp")
            )->groupBy(
                DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')")
            )->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'desc')->get();

        $previousPeriod = collect();
        $period = CarbonPeriod::create($this->from, '1 month', $this->to);

        foreach ($period as $date) {
            // Find our records for this period.
            $report = $results->first(function ($month) use ($date) {
                return $month->monthstamp == $date->format('Ym');
            });
            if (!$report) {
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
            'currentPeriod' => $currentPeriod,
            'previousPeriod' => $previousPeriod,
        ];
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

        // Get our customer groups.
        $groups = FetchCustomerGroups::run([
            'paginate' => false,
        ]);

        return $groups->mapWithKeys(function ($group) use ($queryFormat, $displayFormat) {
            $query = $this->getOrderQuery();

            $guestOrders = null;

            if ($group->default) {
                $guestOrders = $this->getOrderQuery()->whereNull('user_id')->select(
                    DB::RAW('ROUND(AVG(order_total), 0) as order_total'),
                    DB::RAW('ROUND(AVG(delivery_total), 0) as delivery_total'),
                    DB::RAW('ROUND(AVG(discount_total), 0) as discount_total'),
                    DB::RAW('ROUND(AVG(sub_total), 0) as sub_total'),
                    DB::RAW('ROUND(AVG(tax_total), 0) as tax_total'),
                    DB::RAW("DATE_FORMAT(placed_at, '{$displayFormat}') as date")
                )->groupBy(
                    DB::RAW("DATE_FORMAT(placed_at, '{$queryFormat}')")
                )->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'desc')->get();
            }

            $result = $this->getOrderQuery()->
                join('users', 'users.id', '=', 'orders.user_id')
                ->join('customers', 'customers.id', '=', 'users.customer_id')
                ->join('customer_customer_group', function ($join) use ($group) {
                    $join->on('customer_customer_group.customer_id', '=', 'customers.id')
                        ->where('customer_customer_group.customer_group_id', '=', $group->id);
                })
                ->select(
                    DB::RAW('ROUND(AVG(order_total), 0) as order_total'),
                    DB::RAW('ROUND(AVG(delivery_total), 0) as delivery_total'),
                    DB::RAW('ROUND(AVG(discount_total), 0) as discount_total'),
                    DB::RAW('ROUND(AVG(sub_total), 0) as sub_total'),
                    DB::RAW('ROUND(AVG(tax_total), 0) as tax_total'),
                    DB::RAW("DATE_FORMAT(placed_at, '{$displayFormat}') as date")
                )->groupBy(
                    DB::RAW("DATE_FORMAT(placed_at, '{$queryFormat}')"),
                    'customer_customer_group.customer_group_id'
                )->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'desc')->get();

            return [$group->handle => [
                'label' => $group->name,
                'handle' => $group->handle,
                'default' => $group->default,
                'data' => $result->map(function ($order) use ($guestOrders) {
                    $data = [
                        'date' => $order->date,
                        'sub_total' => (int) $order->sub_total,
                        'delivery_total' => (int) $order->delivery_total,
                        'tax_total' => (int) $order->tax_total,
                        'order_total' => (int) $order->order_total,
                        'discount_total' => (int) $order->discount_total,
                    ];

                    if ($guestOrders) {
                        $period = $guestOrders->first(function ($orders) use ($order) {
                            return $order->date == $orders->date;
                        });
                        if ($period) {
                            $data['sub_total'] += $period->sub_total;
                            $data['delivery_total'] += $period->delivery_total;
                            $data['tax_total'] += $period->tax_total;
                            $data['order_total'] += $period->order_total;
                            $data['discount_total'] += $period->discount_total;
                        }
                    }

                    return $data;
                })
            ]];
        });
        // $orders = $this->getOrderQuery()
        //     ->select(
        //         DB::RAW('ROUND(AVG(order_total), 0) as order_total'),
        //         DB::RAW('ROUND(AVG(delivery_total), 0) as delivery_total'),
        //         DB::RAW('ROUND(AVG(discount_total), 0) as discount_total'),
        //         DB::RAW('ROUND(AVG(sub_total), 0) as sub_total'),
        //         DB::RAW('ROUND(AVG(tax_total), 0) as tax_total'),
        //         DB::RAW("DATE_FORMAT(placed_at, '{$displayFormat}') as date")
        //     )->groupBy(
        //         DB::RAW("DATE_FORMAT(placed_at, '{$queryFormat}')")
        //     )->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'desc')->get();

        // return $orders->map(function ($order) {
        //     return [
        //         'date' => $order->date,
        //         'sub_total' => $order->sub_total,
        //         'delivery_total' => $order->delivery_total,
        //         'tax_total' => $order->tax_total,
        //         'order_total' => $order->order_total,
        //         'discount_total' => $order->discount_total,
        //     ];
        // });
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
