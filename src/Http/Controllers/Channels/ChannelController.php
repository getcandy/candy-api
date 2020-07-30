<?php

namespace GetCandy\Api\Http\Controllers\Channels;

use GetCandy;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Channels\CreateRequest;
use GetCandy\Api\Http\Requests\Channels\DeleteRequest;
use GetCandy\Api\Http\Requests\Channels\UpdateRequest;
use GetCandy\Api\Http\Resources\Channels\ChannelCollection;
use GetCandy\Api\Http\Resources\Channels\ChannelResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ChannelController extends BaseController
{
    /**
     * Returns a listing of channels.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Channels\ChannelCollection
     */
    public function index(Request $request)
    {
        $paginator = GetCandy::channels()->getPaginatedData($request->per_page);

        return new ChannelCollection($paginator);
    }

    /**
     * Handles the request to show a channel based on it's hashed ID.
     *
     * @param  string  $id
     * @return array|\GetCandy\Api\Http\Resources\Channels\ChannelResource
     */
    public function show($id)
    {
        try {
            $channel = GetCandy::channels()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new ChannelResource($channel);
    }

    /**
     * Handles the request to create a new channel.
     *
     * @param  \GetCandy\Api\Http\Requests\Channels\CreateRequest  $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        return new ChannelResource(
            GetCandy::channels()->create($request->all())
        );
    }

    /**
     * Handles the request to update  a channel.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Channels\UpdateRequest  $request
     * @return array
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $channel = GetCandy::channels()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new ChannelResource($channel);
    }

    /**
     * Handles the request to delete a channel.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Channels\DeleteRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            GetCandy::channels()->delete($id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
