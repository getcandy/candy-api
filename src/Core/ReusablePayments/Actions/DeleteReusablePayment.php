<?php

namespace GetCandy\Api\Core\ReusablePayments\Actions;

use Lorisleiva\Actions\Action;
use GetCandy\Api\Core\Payments\PaymentContract;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Core\ReusablePayments\Models\ReusablePayment;

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
    public function handle(PaymentContract $payments)
    {
        if (! $this->reusablePayment) {
            return false;
        }

        $driver = $payments->with(
            $this->reusablePayment->provider
        );

        if (method_exists($driver, 'deleteReusablePayment')) {
            $driver->deleteReusablePayment(
                $this->reusablePayment
            );
        }
        return $this->reusablePayment->delete();
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
