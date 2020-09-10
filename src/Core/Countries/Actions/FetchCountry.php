<?php

namespace GetCandy\Api\Core\Countries\Actions;

use GetCandy\Api\Core\Countries\Models\Country;
use Lorisleiva\Actions\Action;

class FetchCountry extends Action
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
            'id' => 'integer|exists:countries|required_without_all:encoded_id,name',
            'encoded_id' => 'string|hashid_is_valid:'.Country::class.'|required_without_all:id,name',
            'name' => 'nullable|string|required_without_all:id,encoded_id',
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

        if ($this->name) {
            return Country::whereName($this->name)->first();
        }

        return Country::findOrFail($this->id);
    }
}
