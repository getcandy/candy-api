<?php

namespace GetCandy\Api\Core\Reports\Actions;

use GetCandy\Api\Core\Reports\Models\ReportExport;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Facades\Storage;

class DownloadReportExport extends AbstractAction
{
    public function authorize()
    {
        return $this->request->hasValidSignature();
    }

    public function rules()
    {
        return [];
    }

    public function handle($id)
    {
        $realId = (new ReportExport())->decodeId($id);
        $export = ReportExport::findOrFail($realId);

        try {
            return Storage::download("{$export->path}/{$export->filename}");
        } catch (\Exception $e) {
            abort(404);
        }
    }
}
