<?php

namespace GetCandy\Api\Http\Requests\Assets;

use GetCandy\Api\Http\Requests\FormRequest;

class UploadRequest extends FormRequest
{
    public function authorize()
    {
        // return $this->user()->can('create', Attribute::class);
        return true;
    }

    public function rules()
    {
        return [
           'file' => 'required_without_all:url,mime_type|max:'.config('assets.max_filesize').'|mimes:'.config('assets.allowed_filetypes'),
           'url' => 'required_with:url|required_without:file|url|asset_url:'.$this->mime_type,
           'mime_type' => 'required_with:url|in:youtube,vimeo,external',
        ];
    }
}
