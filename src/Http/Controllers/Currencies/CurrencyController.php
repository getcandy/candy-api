<?php

namespace GetCandy\Api\Http\Controllers\Currencies;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Currencies\CreateRequest;
use GetCandy\Api\Http\Requests\Currencies\DeleteRequest;
use GetCandy\Api\Http\Requests\Currencies\UpdateRequest;
use GetCandy\Api\Http\Transformers\Fractal\Currencies\CurrencyTransformer;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CurrencyController extends BaseController
{
    /**
     * Returns a listing of currencies.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index(Request $request)
    {
        $paginator = app('api')->currencies()->getPaginatedData($request->per_page);

        return $this->respondWithCollection($paginator, new CurrencyTransformer);
    }

    /**
     * Handles the request to show a currency based on it's hashed ID.
     * 
     * @param  string  $code
     * @return array
     */
    public function show($code)
    {
        try {
            $currency = app('api')->currencies()->getByCode($code);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($currency, new CurrencyTransformer);
    }

    /**
     * Handles the request to create a new currency.
     * 
     * @param  \GetCandy\Api\Http\Requests\Currencies\CreateRequest  $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        $result = app('api')->currencies()->create($request->all());

        return $this->respondWithItem($result, new CurrencyTransformer);
    }

    public function update($id, UpdateRequest $request)
    {
        try {
            $result = app('api')->currencies()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($result, new CurrencyTransformer);
    }

    /**
     * Handles the request to delete a currency.
     * 
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Currencies\DeleteRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            $result = app('api')->currencies()->delete($id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
