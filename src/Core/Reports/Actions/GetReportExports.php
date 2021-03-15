<?php

namespace GetCandy\Api\Core\Reports\Actions;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Reports\Models\ReportExport;
use GetCandy\Api\Core\Reports\Resources\ReportExportCollection;

class GetReportExports extends AbstractAction
{
    public function rules()
    {
        return [];
    }

    public function handle()
    {
        return ReportExport::whereUserId($this->user()->id)->whereNotNull('completed_at')->paginate(25);
    }

    public function response($result, $request)
    {
        return new ReportExportCollection($result);
    }
}