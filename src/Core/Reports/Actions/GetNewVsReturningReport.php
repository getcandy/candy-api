<?php

namespace GetCandy\Api\Core\Reports\Actions;

use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;
use GetCandy\Api\Core\Reports\Models\ReportExport;
use GetCandy\Api\Core\Reports\Resources\ReportExportResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Carbon;

class GetNewVsReturningReport extends AbstractAction
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
            'New',
            'Returning',
            'Total',
        ];
    }

    public function getExportFilename()
    {
        return 'new_vs_returning_customers_'.$this->from.'-'.$this->to;
    }

    public function getCsvRow($row)
    {
        return [
            $row['label'],
            $row['new'],
            $row['returning'],
            $row['total'],
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
                'report' => 'new-vs-returning-customers-report',
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
            )->customers();

        return $report;
    }
}
