<?php

namespace GetCandy\Api\Http\Controllers\Taxes;

use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Taxes\CreateRequest;
use GetCandy\Api\Http\Requests\Taxes\DeleteRequest;
use GetCandy\Api\Http\Requests\Taxes\UpdateRequest;
use GetCandy\Api\Http\Transformers\Fractal\Taxes\TaxTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaxController extends BaseController
{
    /**
     * Returns a listing of taxes.
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index(Request $request)
    {
        $paginator = app('api')->taxes()->getPaginatedData($request->per_page);

        return $this->respondWithCollection($paginator, new TaxTransformer);
    }

    /**
     * Handles the request to show a tax based on it's hashed ID.
     * 
     * @param  string  $id
     * @return array
     */
    public function show($id)
    {
        try {
            $currency = app('api')->taxes()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($currency, new TaxTransformer);
    }

    /**
     * Handles the request to create a new tax.
     * 
     * @param  \GetCandy\Api\Http\Requests\Taxes\CreateRequest  $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        $result = app('api')->taxes()->create($request->all());

        return $this->respondWithItem($result, new TaxTransformer);
    }

    /**
     * Handles the request to update taxes.
     * 
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Taxes\UpdateRequest  $request
     * @return array
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = app('api')->taxes()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($result, new TaxTransformer);
    }

    /**
     * Handles the request to delete a tax.
     * 
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Taxes\DeleteRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            $result = app('api')->taxes()->delete($id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
