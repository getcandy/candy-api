<?php

namespace GetCandy\Api\Core\Addresses\Actions;

use GetCandy;
use Lorisleiva\Actions\Action;
use GetCandy\Api\Core\Addresses\Models\Address;

class FetchAddressAction extends Action
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
            'id' => 'integer|exists:addresses|required_without:encoded_id',
            'encoded_id' => 'string|hashid_is_valid:addresses|required_without:id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->encoded_id) {
            $this->id = (new Address)->decodeId($this->encoded_id);
        }
        return Address::findOrFail($this->id);
    }
}
