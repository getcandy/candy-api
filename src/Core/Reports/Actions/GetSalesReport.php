<?php

namespace GetCandy\Api\Core\Reports\Actions;

use Illuminate\Support\Carbon;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Reports\Models\ReportExport;
use GetCandy\Api\Core\Reports\Actions\ExportReport;
use GetCandy\Api\Core\Reports\Resources\ReportExportResource;
use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;

class GetSalesReport extends AbstractAction
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
            'paginate' => 'nullable',
            'mode' => 'nullable'
        ];
    }

    public function getCsvHeaders()
    {
        return [
            'Month',
            'Orders',
            'Revenue'
        ];
    }

    public function getExportFilename()
    {
        return 'order-sales_' . $this->from . '-' . $this->to;
    }

    public function getExportData($args)
    {
        $result = $this->run($args);

        $orders = $result['datasets'][0]['data'];
        $revenue = $result['datasets'][1]['data'];

        $data = [];

        foreach  ($orders as $index => $orderValue) {
            $data[] = [
                'month' => $result['labels'][$index],
                'orders' => $orderValue,
                'revenue' => $revenue[$index] / 100
            ];
        }
        return $data;
    }

    public function getCsvRow($row)
    {
        return [
            $row['month'],
            $row['orders'],
            $row['revenue'],
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle(ReportManagerContract $reports)
    {
        if ($this->export) {
            // Create the export
            $export = ReportExport::create([
                'user_id' => $this->user()->id,
                'report' => 'sales-report',
                'started_at' => now(),
            ]);
            ExportReport::dispatch([
                'report' => self::class,
                'export' => $export,
                'args' => $this->validated(),
            ]);
            return new ReportExportResource($export);
        }

        $report = $reports->with('sales')
            ->mode($this->mode ?: 'monthly')
            ->between(
                Carbon::parse($this->from),
                Carbon::parse($this->to)
            )->get();

        return $report;
    }
}
