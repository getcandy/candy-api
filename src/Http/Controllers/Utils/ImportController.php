<?php

namespace GetCandy\Api\Http\Controllers\Utils;

use Carbon\Carbon;
use GetCandy\Api\Jobs\Utils\ImportJob;
use GetCandy\Api\Core\Utils\Import\Models\Import;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Utils\ProcessImportRequest;

class ImportController extends BaseController
{
    public function process(ProcessImportRequest $request)
    {
        $path = Carbon::now()->format('Y/m/d');

        $store = $request->file('file')->store("imports/{$path}");

        $import = Import::create([
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'file' => $store,
            'email' => $request->email,
        ]);

        ImportJob::dispatch($import);

        return response()->json([
            'processed' => true,
        ]);
    }
}
