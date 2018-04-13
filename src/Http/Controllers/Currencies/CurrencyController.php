<?php

namespace GetCandy\Api\Http\Controllers\Currencies;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Requests\Currencies\CreateRequest;
use GetCandy\Api\Http\Requests\Currencies\DeleteRequest;
use GetCandy\Api\Http\Requests\Currencies\UpdateRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Currencies\CurrencyTransformer;

class CurrencyController extends BaseController
{
    /**
     * Returns a listing of currencies.
     * @return Json
     */
    public function index(Request $request)
    {
        $paginator = app('api')->currencies()->getPaginatedData($request->per_page);

        return $this->respondWithCollection($paginator, new CurrencyTransformer);
    }

    /**
     * Handles the request to show a currency based on it's hashed ID.
     * @param  string $id
     * @return Json
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
     * Handles the request to create a new channel.
     * @param  CreateRequest $request
     * @return Json
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
     * @param  string        $id
     * @param  DeleteRequest $request
     * @return Json
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
