<?php

namespace GetCandy\Api\Core\ReusablePayments\Actions;

use GetCandy\Api\Core\ReusablePayments\Models\ReusablePayment;
use Lorisleiva\Actions\Action;

class FetchReusablePayment extends Action
{
    /**
     * The fetched reusable payment model.
     *
     * @var \GetCandy\Api\Core\ReusablePayments\Models\ReusablePayment
     */
    protected $reusablePayment;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->encoded_id) {
            $this->id = (new ReusablePayment)->decodeId($this->encoded_id);
        }

        $this->reusablePayment = ReusablePayment::findOrFail($this->id);

        return $this->user()->can('view', $this->reusablePayment);
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'integer|exists:reusable_payments|required_without:encoded_id',
            'encoded_id' => 'string|hashid_is_valid:'.ReusablePayment::class.'|required_without:id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        return $this->reusablePayment;
    }
}
