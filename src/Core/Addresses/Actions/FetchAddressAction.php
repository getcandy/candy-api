<?php

namespace GetCandy\Api\Core\Addresses\Actions;

use GetCandy\Api\Core\Addresses\Models\Address;
use Lorisleiva\Actions\Action;

class FetchAddressAction extends Action
{
    /**
     * The fetched address model.
     *
     * @var \GetCandy\Api\Core\Addresses\Models\Address
     */
    protected $address;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->encoded_id) {
            $this->id = (new Address)->decodeId($this->encoded_id);
        }
        $this->address = Address::findOrFail($this->id);

        return $this->user()->can('view', $this->address);
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
            'encoded_id' => 'string|hashid_is_valid:'.Address::class.'|required_without:id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->address;
    }
}
