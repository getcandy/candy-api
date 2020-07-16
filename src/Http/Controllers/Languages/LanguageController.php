<?php

namespace GetCandy\Api\Http\Controllers\Languages;

use GetCandy;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Languages\CreateRequest;
use GetCandy\Api\Http\Requests\Languages\DeleteRequest;
use GetCandy\Api\Http\Requests\Languages\UpdateRequest;
use GetCandy\Api\Http\Resources\Languages\LanguageCollection;
use GetCandy\Api\Http\Resources\Languages\LanguageResource;
use GetCandy\Api\Http\Transformers\Fractal\Languages\LanguageTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LanguageController extends BaseController
{
    /**
     * Returns a listing of languages.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \GetCandy\Api\Http\Resources\Languages\LanguageCollection
     */
    public function index(Request $request)
    {
        $paginator = GetCandy::languages()->getPaginatedData($request->per_page);

        return new LanguageCollection($paginator);
    }

    /**
     * Returns a single Language.
     *
     * @param  string  $id
     * @return array
     */
    public function show($id)
    {
        try {
            $language = GetCandy::languages()->getByHashedId($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($language, new LanguageTransformer);
    }

    /**
     * Handles the request to create a new language.
     *
     * @param  \GetCandy\Api\Http\Requests\Languages\CreateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Languages\LanguageResource
     */
    public function store(CreateRequest $request)
    {
        $result = GetCandy::languages()->create($request->all());

        return new LanguageResource($result);
    }

    /**
     * Handles the request to update a language.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Languages\UpdateRequest  $request
     * @return array
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $result = GetCandy::languages()->update($id, $request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($result, new LanguageTransformer);
    }

    /**
     * Handles the request to delete a language.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Languages\DeleteRequest  $request
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, DeleteRequest $request)
    {
        try {
            $result = GetCandy::languages()->delete($id);
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
