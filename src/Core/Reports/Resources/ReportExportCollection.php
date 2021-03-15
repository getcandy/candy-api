<?php

namespace GetCandy\Api\Core\Reports\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use GetCandy\Api\Core\Reports\Resources\ReportExportResource;

class ReportExportCollection extends ResourceCollection
{
    public $collects = ReportExportResource::class;
}