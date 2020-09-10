<?php

namespace GetCandy\Api\Core\Currencies\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Currencies\Models\Currency;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Core\Currencies\Resources\CurrencyResource;

class FetchCurrency extends AbstractAction
{
    use ReturnsJsonResponses;

    /**
     * The fetched address model.
     *
     * @var \GetCandy\Api\Core\Currencies\Models\Currency
     */
    protected $currency;

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($this->encoded_id && ! $this->handle) {
            $this->id = (new Currency)->decodeId($this->encoded_id);
        }

        try {
            $query = Currency::with($this->resolveEagerRelations());

            if ($this->search) {
                $query = $this->compileSearchQuery($query, $this->search);
            }
            $this->currency = $query->withCount($this->resolveRelationCounts())
                ->findOrFail($this->id);
        } catch (ModelNotFoundException $e) {
            if (! $this->runningAs('controller')) {
                throw $e;
            }
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id' => 'integer|required_without:encoded_id',
            'encoded_id' => 'string|hashid_is_valid:'.Currency::class.'|required_without:id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Currencies\Models\Currency|null
     */
    public function handle()
    {
        return $this->currency;
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Languages\Models\Language  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Currencies\Resources\CurrencyResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return new CurrencyResource($result);
    }
}
