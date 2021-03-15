<?php

namespace GetCandy\Api\Core\Reports\Actions;

use GetCandy;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Reports\Models\ReportExport;
use GetCandy\Api\Core\Reports\Resources\ReportExportResource;

class GetCustomerSpendingReport extends AbstractAction
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
            'export' => 'nullable|boolean',
            'paginate' => 'nullable',
        ];
    }

    public function getCsvHeaders ()
    {
        return [
            'has_account',
            'name',
            'email',
            'total_spent',
        ];
    }

    public function getExportFilename()
    {
        return 'customer-spending_' . $this->from . '-' . $this->to;
    }

    public function getCsvRow($row)
    {
        return [
            !!$row->user_id,
            "{$row->firstname} {$row->lastname}",
            $row->email,
            $row->sub_total / 100
        ];
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
                'report' => 'customer-spending',
                'started_at' => now(),
            ]);
            ExportReport::dispatch([
                'report' => self::class,
                'export' => $export,
                'args' => $this->validated(),
            ]);
            return new ReportExportResource($export);
        }

        $paginate = $this->get('paginate', true);

        $result = Order::whereNotNull('placed_at')
        ->select(
            DB::RAW('SUM(sub_total) as sub_total'),
            'billing_email as email',
            'billing_firstname as firstname',
            'billing_lastname as lastname',
            'billing_company_name as company_name',
            'users.id as user_id'
        )->whereNotNull('billing_email')->whereBetween('placed_at', [
            $this->from,
            $this->to,
        ])->where('sub_total', '>', 100)
        ->leftJoin('users', function ($join) {
            $join->on('users.id', '=', 'orders.user_id')->whereNotNull('orders.user_id');
        })
        ->groupBy('billing_email')->orderBy('sub_total', 'desc')
        ->orderBy(DB::RAW("DATE_FORMAT(placed_at, '%Y-%m')"), 'asc');

        if ($paginate) {
            $result = $result->paginate(50);
            $items = $result->getCollection()->map(function ($row) {
                $userModel = GetCandy::getUserModel();
    
                return array_merge($row->toArray(), [
                    'user_id' => $row->user_id ? (new $userModel)->encode($row->user_id) : null,
                ]);
            });
            $result->setCollection($items);
        }

        return [
            'period' => [
                'from' => $this->from,
                'to' => $this->to,
            ],
            'data' => $paginate ? $result : $result->get(),
        ];
    }
}
