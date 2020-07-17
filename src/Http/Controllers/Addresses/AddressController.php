<?php

namespace GetCandy\Api\Http\Controllers\Addresses;

use GetCandy;
use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Resources\Addresses\AddressResource;

class AddressController extends BaseController
{
    public function update($id, Request $request)
    {
        try {
            $address = GetCandy::addresses()->update($id, $request->all());
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new AddressResource($address);
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

        return new AddressResource($address);
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

        return new AddressResource($address);
    }

    public function removeDefault($id, Request $request)
    {
        try {
            $address = GetCandy::addresses()->removeDefault($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new AddressResource($address);
    }
}
