<?php

namespace GetCandy\Api\Core\Reports\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportExportCollection extends ResourceCollection
{
    public $collects = ReportExportResource::class;
}
