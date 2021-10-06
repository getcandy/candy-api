<?php

namespace GetCandy\Api\Core\Reports\Actions;

use GetCandy\Api\Core\Reports\Mail\ReportExported;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ExportReport extends AbstractAction
{
    public function rules()
    {
        return [
            'report' => 'required|string',
            'export' => 'required',
            'args' => 'required',
        ];
    }

    public function handle()
    {
        $args = array_merge($this->args, [
            'export' => false,
            'paginate' => false,
        ]);

        $report = (new $this->report())->actingAs($this->user());

        if (method_exists($this->report, 'getExportData')) {
            $result = $report->getExportData($args);
        } else {
            $result = $report->run($args);
        }

        $result = $result['data'] ?? $result;

        // Create our export file...
        $filename = $report->getExportFilename().'.csv';
        $location = 'reporting/exports/'.now()->format('Y/m/d');

        Storage::put("{$location}/{$filename}", null);

        $fp = fopen(storage_path("app/{$location}/{$filename}"), 'wb');

        fputcsv($fp, $report->getCsvHeaders());

        foreach ($result as $row) {
            $val = $report->getCsvRow($row);
            fputcsv($fp, $val);
        }

        $url = URL::signedRoute(
            'export.download',
            ['id' => $this->export->encoded_id]
        );

        Mail::to($this->user()->email)->queue(
            new ReportExported($url)
        );

        $this->export->update([
            'filename' => $filename,
            'path' => $location,
            'completed_at' => now(),
        ]);
    }
}
