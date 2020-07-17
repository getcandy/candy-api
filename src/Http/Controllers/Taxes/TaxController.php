<?php

namespace GetCandy\Api\Http\Controllers\Taxes;

use GetCandy;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Taxes\CreateRequest;
use GetCandy\Api\Http\Requests\Taxes\DeleteRequest;
use GetCandy\Api\Http\Requests\Taxes\UpdateRequest;
use GetCandy\Api\Http\Resources\Taxes\TaxCollection;
use GetCandy\Api\Http\Resources\Taxes\TaxResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TaxController extends BaseController
{
    /**
     * Returns a listing of taxes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Taxes\TaxCollection
     */
    public function index(Request $request)
    {
        $paginator = GetCandy::taxes()->getPaginatedData($request->per_page);

        return new TaxCollection($paginator);
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
            $tax = GetCandy::taxes()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new TaxResource($tax);
    }

    /**
     * Handles the request to create a new tax.
     *
     * @param  \GetCandy\Api\Http\Requests\Taxes\CreateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Taxes\TaxResource
     */
    public function store(CreateRequest $request)
    {
        return new TaxResource(
            GetCandy::taxes()->create($request->all())
        );
    }

    /**
     * Handles the request to update taxes.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Taxes\UpdateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Taxes\TaxResource
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $tax = GetCandy::taxes()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new TaxResource($tax);
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
            GetCandy::taxes()->delete($id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
