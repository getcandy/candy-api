<?php

namespace GetCandy\Api\Core\Reports\Actions;

use Carbon\CarbonPeriod;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroups;
use GetCandy\Api\Core\Reports\Models\ReportExport;
use GetCandy\Api\Core\Reports\Resources\ReportExportResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Facades\DB;

class GetOrderAveragesReport extends AbstractAction
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
            'export' => 'nullable|boolean',
        ];
    }

    public function getCsvHeaders()
    {
        $period = CarbonPeriod::create($this->from, '1 month', $this->to);
        $headers = ['Customer Group'];
        foreach ($period as $date) {
            $headers[] = $date->format('F Y');
        }

        return $headers;
    }

    public function getExportFilename()
    {
        return 'order-averages_'.$this->from.'-'.$this->to;
    }

    public function getCsvRow($row)
    {
        $data = [$row['label']];

        foreach ($row['data'] as $item) {
            $data[] = $item['sub_total'] / 100;
        }

        return $data;
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        if ($this->export) {
            // Create the export
            $export = ReportExport::create([
                'user_id' => $this->user()->id,
                'report' => 'order-averages',
                'started_at' => now(),
            ]);
            ExportReport::dispatch([
                'report' => self::class,
                'export' => $export,
                'args' => $this->validated(),
            ]);

            return new ReportExportResource($export);
        }

        // Get our customer groups.
        $groups = FetchCustomerGroups::run([
            'exclude' => config('getcandy.reports.customer_groups.exclude', []),
            'paginate' => false,
        ]);

        $period = CarbonPeriod::create($this->from, '1 month', $this->to);

        return [
            'period' => collect($period->toArray())->map(function ($date) {
                return [
                    'label' => $date->format('F Y'),
                    'date' => $date,
                ];
            }),
            'data' => $groups->mapWithKeys(function ($group) use ($period) {
                $guestOrders = null;

                if ($group->default) {
                    $guestOrders = $this->getInitialQuery()->whereNull('user_id')->select(
                        DB::RAW('ROUND(AVG(order_total), 0) as order_total'),
                        DB::RAW('ROUND(AVG(delivery_total), 0) as delivery_total'),
                        DB::RAW('ROUND(AVG(discount_total), 0) as discount_total'),
                        DB::RAW('ROUND(AVG(sub_total), 0) as sub_total'),
                        DB::RAW('ROUND(AVG(tax_total), 0) as tax_total'),
                        DB::RAW("DATE_FORMAT(placed_at, '%Y%m') as date")
                    )->groupBy(
                        DB::RAW("DATE_FORMAT(placed_at, '%Y%m')")
                    )->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'desc')->get();
                }

                $result = $this->getInitialQuery()->join('users', 'users.id', '=', 'orders.user_id')
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
                        DB::RAW("DATE_FORMAT(placed_at, '%Y%m') as date")
                    )->groupBy(
                        DB::RAW("DATE_FORMAT(placed_at, '%Y%m')"),
                        'customer_customer_group.customer_group_id'
                    )->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'desc')->get();

                $months = collect();

                foreach ($period as $date) {
                    $record = $result->first(function ($row) use ($date) {
                        return $date->format('Ym') === $row->date;
                    });
                    if (! $record) {
                        $record = (object) [
                            'order_total' => 0,
                            'delivery_total' => 0,
                            'discount_total' => 0,
                            'sub_total' => 0,
                            'tax_total' => 0,
                            'date' => $date->format('Ym'),
                        ];
                    }
                    $months->push($record);
                }

                return [
                    $group->handle => [
                        'label' => $group->name,
                        'handle' => $group->handle,
                        'default' => $group->default,
                        'data' => $months->map(function ($order) use ($guestOrders) {
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
                        }),
                    ],
                ];
            }),
        ];
    }

    protected function getInitialQuery()
    {
        return DB::table('orders')->whereNotNull('placed_at')
        ->whereBetween('placed_at', [
            $this->from,
            $this->to,
        ]);
    }
}
