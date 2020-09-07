<?php

namespace GetCandy\Api\Core\Foundation\Actions;

use Lorisleiva\Actions\Action;

class DecodeId extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'model' => 'required|string',
            'encoded_id' => 'required|string',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return string
     */
    public function handle()
    {
        return (new $this->model)->decodeId($this->encoded_id);
    }
}
