<?php

namespace GetCandy\Api\Core\Reports\Actions;

use Illuminate\Support\Facades\DB;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Reports\Models\ReportExport;
use GetCandy\Api\Core\Reports\Actions\ExportReport;
use GetCandy\Api\Core\Reports\Resources\ReportExportResource;

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
            'export' => 'nullable',
            'paginate' => 'nullable',
        ];
    }

    public function getCsvHeaders ()
    {
        return [
            'product',
            'sku',
            'quantity',
            'sub_total',
        ];
    }

    public function getExportFilename()
    {
        return 'product-best-sellers_' . $this->from . '_' . $this->to;
    }

    public function getCsvRow($row)
    {
        return [
            $row->description,
            $row->sku,
            $row->quantity,
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
                'report' => 'product-best-sellers',
                'started_at' => now(),
            ]);
            ExportReport::dispatch([
                'report' => self::class,
                'export' => $export,
                'args' => $this->validated(),
            ]);
            return new ReportExportResource($export);
        }

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

        $paginate = $this->get('paginate', true);

        return $paginate ? $query->paginate(50) : $query->get();
    }
}
