<?php

namespace GetCandy\Api\Core\Addresses\Actions;

use GetCandy\Api\Core\Addresses\Models\Address;
use Lorisleiva\Actions\Action;

class DeleteAddressAction extends Action
{
    /**
     * The address object we want to update.
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
        $this->address = FetchAddressAction::run([
            'encoded_id' => $this->addressId,
        ]);

        return $this->user()->can('delete', $this->address);
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Addresses\Models\Address
     */
    public function handle()
    {
        $this->address->delete();
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Addresses\Models\Address  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  json
     */
    public function response($result, $request)
    {
        return response()->json([], 204);
    }
}
