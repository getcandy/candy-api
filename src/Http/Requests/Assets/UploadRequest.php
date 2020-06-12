<?php

namespace GetCandy\Api\Http\Requests\Assets;

use GetCandy\Api\Http\Requests\FormRequest;

class UploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // return $this->user()->can('create', Attribute::class);
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required_without_all:url,mime_type|max:'.config('assets.max_filesize').'|mimes:'.config('assets.allowed_filetypes'),
            'url' => 'required_with:url|required_without:file|url|asset_url:'.$this->mime_type,
            'mime_type' => 'required_with:url|in:youtube,vimeo,external',
        ];
    }
}
