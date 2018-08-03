<?php

namespace GetCandy\Api\Http\Controllers\Addresses;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Addresses\AddressTransformer;

class AddressController extends BaseController
{
    public function update($id, Request $request)
    {
        try {
            $address = app('api')->addresses()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($address, new AddressTransformer);
    }

    public function store(Request $request)
    {
        try {
            $user = app('api')->users()->getByHashedId($request->user()->encodedId());
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }
        $address = app('api')->addresses()->create($user, $request->all());

        return $this->respondWithItem($address, new AddressTransformer);
    }

    public function destroy($id)
    {
        try {
            $result = app('api')->addresses()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
