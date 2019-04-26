<?php

namespace GetCandy\Api\Http\Controllers\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;

class ReportController extends BaseController
{
    public function sales(Request $request, ReportManagerContract $reports)
    {
        $this->validate($request, [
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $report = $reports->with('sales')
            ->mode($request->mode ?: 'monthly')
            ->between(
                Carbon::parse($request->from),
                Carbon::parse($request->to)
            )->get();

        return response()->json($report);
    }

    public function metrics(Request $request, ReportManagerContract $reports)
    {
        $report = $reports->with($request->subject)->metrics();

        return response()->json($report);
    }

    public function orders(Request $request, ReportManagerContract $reports)
    {
        $this->validate($request, [
            'from' => 'required|date',
            'to' => 'required|date|after:from',
        ]);

        $report = $reports->with('orders')
            ->mode($request->mode ?: 'monthly')
            ->between(
                Carbon::parse($request->from),
                Carbon::parse($request->to)
            )->get();

        return response()->json($report);
    }
}
