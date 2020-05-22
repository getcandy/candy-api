<?php

namespace GetCandy\Api\Http\Controllers\Versioning;

use GetCandy\Api\Http\Controllers\BaseController;
use Hashids;
use Illuminate\Http\Request;
use NeonDigital\Versioning\Version;
use Versioning;

class VersionController extends BaseController
{
    public function restore($id, Request $request)
    {
        // Get the real id.
        $id = Hashids::decode($id)[0] ?? null;
        $type = $request->type ?: 'products';

        if (! $id) {
            return $this->errorNotFound();
        }

        $version = Version::findOrFail($id);

        return \DB::transaction(function () use ($version, $type) {
            $result = Versioning::with($type)->restore($version);

            return response()->json([
                'id' => $result->encoded_id,
            ]);
        });
    }
}
