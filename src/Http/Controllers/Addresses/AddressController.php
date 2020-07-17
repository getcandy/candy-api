<?php

namespace GetCandy\Api\Http\Controllers\Addresses;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Transformers\Fractal\Addresses\AddressTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AddressController extends BaseController
{
    public function update($id, Request $request)
    {
        try {
            $address = GetCandy::addresses()->update($id, $request->all());
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($address, new AddressTransformer);
    }

    public function store(Request $request)
    {
        try {
            $authUser = $request->user();
            if ($request->user_id && $authUser->hasRole('admin')) {
                $id = $request->user_id;
            } else {
                $id = $authUser->encodedId();
            }
            $user = GetCandy::users()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }
        $address = GetCandy::addresses()->create($user, $request->all());

        return $this->respondWithItem($address, new AddressTransformer);
    }

    public function destroy($id)
    {
        try {
            GetCandy::addresses()->delete($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }

    public function makeDefault($id, Request $request)
    {
        try {
            $address = GetCandy::addresses()->makeDefault($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($address, new AddressTransformer);
    }

    public function removeDefault($id, Request $request)
    {
        try {
            $address = GetCandy::addresses()->removeDefault($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($address, new AddressTransformer);
    }
}
