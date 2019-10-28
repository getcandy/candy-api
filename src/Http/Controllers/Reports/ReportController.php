<?php

namespace GetCandy\Api\Http\Controllers\Reports;

use Carbon\Carbon;
use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;
use GetCandy\Api\Http\Resources\Products\ProductCollection;
use GetCandy\Api\Http\Resources\Products\ProductResource;

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

    public function shipping(Request $request, ReportManagerContract $reports)
    {
        $this->validate($request, [
            'from' => 'required|date',
            'to' => 'required|date|after:from',
        ]);

        $report = $reports->with('shipping')
            ->mode($request->mode ?: 'monthly')
            ->between(
                Carbon::parse($request->from),
                Carbon::parse($request->to)
            )->get();

        return response()->json($report);
    }


    public function productAttributes(Request $request, ReportManagerContract $reports)
    {
        return $reports->with('products')->attribute(
            $request->attribute,
            $request->attribute_value,
            $request->expression
        );
    }


    public function attributes(Request $request, ReportManagerContract $reports)
    {
        return $reports->with('attributes')->attribute($request->attribute);
    }
}
