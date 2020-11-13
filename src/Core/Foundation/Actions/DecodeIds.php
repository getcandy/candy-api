<?php

namespace GetCandy\Api\Core\Foundation\Actions;

use Lorisleiva\Actions\Action;

class DecodeIds extends Action
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
            'encoded_ids' => 'array|min:0',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return string
     */
    public function handle()
    {
        $ids = [];

        foreach ($this->encoded_ids as $id) {
            $ids[] = DecodeId::run([
                'model' => $this->model,
                'encoded_id' => $id,
            ]);
        }

        return $ids;
    }
}
