<?php

namespace GetCandy\Api\Http\Controllers\Reports;

use GetCandy\Api\Core\Reports\Contracts\ReportManagerContract;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    public function metrics(Request $request, ReportManagerContract $reports)
    {
        $report = $reports->with($request->subject)->metrics();

        return response()->json($report);
    }
}
