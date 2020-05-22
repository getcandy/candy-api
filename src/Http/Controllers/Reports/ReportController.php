<?php

namespace GetCandy\Api\Http\Controllers\Reports;

use Carbon\Carbon;
use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Http\Request;

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

    public function orderCustomers(Request $request, ReportManagerContract $reports)
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
            )->customers();

        return response()->json($report);
    }

    public function orderAverages(Request $request, ReportManagerContract $reports)
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
            )->averages();

        return response()->json($report);
    }

    public function bestSellers(Request $request, ReportManagerContract $reports)
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
            )->bestSellers($request->limit);

        return response()->json($report);
    }
}
