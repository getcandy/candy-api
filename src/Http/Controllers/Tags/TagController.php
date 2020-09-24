<?php

namespace GetCandy\Api\Http\Controllers\Tags;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Tags\TagCollection;
use GetCandy\Api\Http\Resources\Tags\TagResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TagController extends BaseController
{
    /**
     * Returns a listing of tags.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Tags\TagCollection
     */
    public function index(Request $request)
    {
        $tags = GetCandy::tags()->getPaginatedData($request->per_page);

        return new TagCollection($tags);
    }

    /**
     * Handles the request to show a tag based on it's hashed ID.
     *
     * @param  string  $id
     * @return array
     */
    public function show($id)
    {
        try {
            $tag = GetCandy::tags()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return new TagResource($tag);
    }

    /**
     * Handles the request to create a new tag.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function store(Request $request)
    {
        return new TagResource(
            GetCandy::tags()->create($request->all())
        );
    }

    /**
     * Handles the request to update a tag.
     *
     * @param  string  $id
     * @param  mixed  $request (?)
     * @return array
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $tag = GetCandy::tags()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new TagResource($tag);
    }

    /**
     * Handles the request to delete a tag.
     *
     * @param  string  $id
     * @param  mixed  $request (?)
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            GetCandy::tags()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
