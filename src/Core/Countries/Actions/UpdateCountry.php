<?php

namespace GetCandy\Api\Core\Countries\Actions;

use GetCandy\Api\Core\Countries\Models\Country;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Action;

class UpdateCountry extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-countries');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'integer|exists:countries|required_without:encoded_id',
            'encoded_id' => 'string|hashid_is_valid:'.Country::class.'|required_without:id',
            'preferred' => 'nullable|boolean',
            'enabled' => 'nullable|boolean',
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

        $country = Country::findOrFail($this->id);

        $country->update(Arr::except($this->validated(), ['id', 'encoded_id']));

        return $country;
    }
}
