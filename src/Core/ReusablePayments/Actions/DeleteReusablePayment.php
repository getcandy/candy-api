<?php

namespace GetCandy\Api\Core\ReusablePayments\Actions;

use GetCandy\Api\Core\ReusablePayments\Models\ReusablePayment;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Lorisleiva\Actions\Action;

class DeleteReusablePayment extends Action
{
    use ReturnsJsonResponses;

    /**
     * The reusable payment object we want to delete.
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
        $this->reusablePayment = FetchReusablePayment::run([
            'encoded_id' => $this->encoded_id,
        ]);

        return $this->user()->can('delete', $this->reusablePayment);
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'integer|required_without_all:encoded_id',
            'encoded_id' => 'string|hashid_is_valid:'.ReusablePayment::class.'|required_without_all:id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! $this->reusablePayment) {
            return false;
        }

        return true;
    }

    /**
     * Returns the response from the action.
     *
     * @param   array  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
