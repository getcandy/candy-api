<?php

namespace GetCandy\Api\Core\Reports\Actions;

use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;
use GetCandy\Api\Core\Reports\Models\ReportExport;
use GetCandy\Api\Core\Reports\Resources\ReportExportResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Carbon;

class GetOrderTotalsReport extends AbstractAction
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
            'mode' => 'nullable',
        ];
    }

    public function getCsvHeaders()
    {
        return [
            'Month',
            'Current Period',
            'Previous Period',
        ];
    }

    public function getExportFilename()
    {
        return 'order-totals_'.$this->from.'-'.$this->to;
    }

    public function getExportData($args)
    {
        $result = $this->run($args);

        $currentPeriod = $result['currentPeriod'];
        $previousPeriod = $result['previousPeriod'];

        $data = [];

        foreach ($currentPeriod as $index => $totals) {
            $previous = collect($previousPeriod)->first(function ($t) use ($totals) {
                return $t->month === $totals->month && $t->year == $totals->year - 1;
            });

            $data[] = [
                'month' => "{$totals->month} {$totals->year}",
                'sub_total' => $totals->sub_total / 100,
                'previous_sub_total' => ($previous->sub_total ?? 0) / 100,
            ];
        }

        return $data;
    }

    public function getCsvRow($row)
    {
        return [
            $row['month'],
            $row['sub_total'],
            $row['previous_sub_total'],
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

        $report = $reports->with('orders')
            ->mode($this->mode ?: 'monthly')
            ->between(
                Carbon::parse($this->from),
                Carbon::parse($this->to)
            )->get();

        return $report;
    }
}
