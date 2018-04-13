<?php

namespace GetCandy\Api\Http\Controllers\Channels;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Channels\CreateRequest;
use GetCandy\Api\Http\Requests\Channels\DeleteRequest;
use GetCandy\Api\Http\Requests\Channels\UpdateRequest;
use GetCandy\Exceptions\MinimumRecordRequiredException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Channels\ChannelTransformer;

class ChannelController extends BaseController
{
    /**
     * Returns a listing of channels.
     * @return Json
     */
    public function index(Request $request)
    {
        $paginator = app('api')->channels()->getPaginatedData($request->per_page);

        return $this->respondWithCollection($paginator, new ChannelTransformer);
    }

    /**
     * Handles the request to show a channel based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $channel = app('api')->channels()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($channel, new ChannelTransformer);
    }

    /**
     * Handles the request to create a new channel.
     * @param  CreateRequest $request
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        $result = app('api')->channels()->create($request->all());

        return $this->respondWithItem($result, new ChannelTransformer);
    }

    /**
     * Handles the request to update  a channel.
     * @param  string        $id
     * @param  UpdateRequest $request
     * @return Json
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = app('api')->channels()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($result, new ChannelTransformer);
    }

    /**
     * Handles the request to delete a channel.
     * @param  string        $id
     * @param  DeleteRequest $request
     * @return Json
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            $result = app('api')->channels()->delete($id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
