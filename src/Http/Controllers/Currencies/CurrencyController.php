<?php

namespace GetCandy\Api\Http\Controllers\Currencies;

use GetCandy;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Currencies\CreateRequest;
use GetCandy\Api\Http\Requests\Currencies\DeleteRequest;
use GetCandy\Api\Http\Requests\Currencies\UpdateRequest;
use GetCandy\Api\Http\Resources\Currencies\CurrencyCollection;
use GetCandy\Api\Http\Resources\Currencies\CurrencyResource;
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
        $paginator = GetCandy::currencies()->getPaginatedData($request->per_page);

        return new CurrencyCollection($paginator);
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
            $currency = GetCandy::currencies()->getByCode($code);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new CurrencyResource($currency);
    }

    /**
     * Handles the request to create a new currency.
     *
     * @param  \GetCandy\Api\Http\Requests\Currencies\CreateRequest  $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        return new CurrencyResource(
            GetCandy::currencies()->create($request->all())
        );
    }

    public function update($id, UpdateRequest $request)
    {
        try {
            $currency = GetCandy::currencies()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new CurrencyResource($currency);
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
            GetCandy::currencies()->delete($id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
