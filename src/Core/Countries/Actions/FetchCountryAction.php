<?php

namespace GetCandy\Api\Core\Countries\Actions;

use GetCandy;
use Lorisleiva\Actions\Action;
use GetCandy\Api\Core\Countries\Models\Country;

class FetchCountryAction extends Action
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
            'id' => 'integer|exists:users|required_without:encoded_id',
            'encoded_id' => 'string|hashid_is_valid:countries|required_without:id',
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
            $this->id = (new Country)->decodeId($this->encoded_id);
        }
        return Country::findOrFail($this->id);
    }
}
