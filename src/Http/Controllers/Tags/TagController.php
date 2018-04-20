<?php

namespace GetCandy\Api\Http\Controllers\Tags;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Transformers\Fractal\Tags\TagTransformer;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TagController extends BaseController
{
    /**
     * Returns a listing of channels.
     * @return Json
     */
    public function index(Request $request)
    {
        $tags = app('api')->tags()->getPaginatedData($request->per_page);

        return $this->respondWithCollection($tags, new TagTransformer);
    }

    /**
     * Handles the request to show a tag based on it's hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id)
    {
        try {
            $tag = app('api')->tags()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($tag, new TagTransformer);
    }

    /**
     * Handles the request to create a new tag.
     * @param  CreateRequest $request
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        $tag = app('api')->tags()->create($request->all());

        return $this->respondWithItem($tag, new TagTransformer);
    }

    /**
     * Handles the request to update a tag.
     * @param  string        $id
     * @param  UpdateRequest $request
     * @return Json
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $tag = app('api')->tags()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($tag, new TagTransformer);
    }

    /**
     * Handles the request to delete a tag.
     * @param  string        $id
     * @param  DeleteRequest $request
     * @return Json
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            $result = app('api')->tags()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
