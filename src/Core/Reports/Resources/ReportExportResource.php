<?php

namespace GetCandy\Api\Core\Reports\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ReportExportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $content = null;

        if ($this->path) {
            try {
                $content = base64_encode(Storage::get("{$this->path}/{$this->filename}"));
            } catch (\Exception $e) {
            }
        }

        return [
            'id' => $this->encodedId(),
            'filename' => $this->filename,
            'report' => $this->report,
            'path' => $this->path,
            'content' => $content,
            'started_at' => $this->started_at,
            'completed_at' => $this->completed_at,
        ];
    }
}
