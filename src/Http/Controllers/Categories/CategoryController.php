<?php

namespace GetCandy\Api\Http\Controllers\Categories;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Core\Categories\CategoryCriteria;
use Intervention\Image\Exception\NotFoundException;
use GetCandy\Api\Http\Requests\Categories\CreateRequest;
use GetCandy\Api\Http\Requests\Categories\DeleteRequest;
use GetCandy\Api\Http\Requests\Categories\UpdateRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Requests\Categories\ReorderRequest;
use GetCandy\Api\Http\Resources\Categories\CategoryResource;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryFancytreeTransformer;

class CategoryController extends BaseController
{
    public function index(Request $request, CategoryCriteria $criteria)
    {
        $criteria
            ->tree($request->tree)
            ->depth($request->depth)
            ->include($request->includes)
            ->limit($request->limit);

        if (! $request->tree) {
            $criteria
                ->page($request->page);
        }

        return new CategoryCollection(
            $criteria->get(),
            $this->parseIncludedFields($request)
        );
    }

    public function children($id, Request $request, CategoryCriteria $criteria)
    {
        $category = $criteria->id($id)->first();

        $query = $category
            ->children()
            ->with($request->includes)
            ->withCount(['products', 'children']);

        return new CategoryCollection($query->get());
    }

    public function show($id, Request $request, CategoryCriteria $criteria)
    {
        $category = $criteria
            ->channel($request->channel)
            ->include($request->includes)
            ->id($id)
            ->first();

        if (! $category) {
            return $this->errorNotFound();
        }
        // try {
        //     $category = $categories->with(
        //         explode(',', $request->includes)
        //     )->getByHashedId($id);
        // } catch (ModelNotFoundException $e) {
        //     return $this->errorNotFound();
        // }

        $resource = new CategoryResource($category);
        $resource->only($this->parseIncludedFields($request));

        return $resource;
        // return $this->respondWithItem($category, new CategoryTransformer);
    }

    public function getNested()
    {
        $categories = app('api')->categories()->getNestedList();

        return $this->respondWithCollection($categories, new CategoryTransformer);
    }

    public function getByParent($parentID = null)
    {
        $categories = app('api')->categories()->getByParentID($parentID);

        return $this->respondWithCollection($categories, new CategoryFancytreeTransformer);
    }

    /**
     * Create new category from basic information.
     * @param $request
     * @request String name
     * @request String slug
     * @request String parent_id (Optional)
     * @return array|\Illuminate\Http\Response
     */
    public function store(CreateRequest $request)
    {
        try {
            $response = app('api')->categories()->create($request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }

        if ($response) {
            return $this->respondWithItem($response, new CategoryTransformer);
        }

        return response()->json('Error', 500);
    }

    /**
     * Handles the request to reorder the categories.
     * @param $request
     * @request String id
     * @request String siblings
     * @request String parent_id (Optional)
     * @return array \Illuminate\Http\Response
     */
    public function reorder(ReorderRequest $request)
    {
        try {
            $response = app('api')->categories()->reorder($request->all());
        } catch (MinimumRecordRequiredException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        } catch (HttpException $e) {
            return $this->errorUnprocessable($e->getMessage());
        } catch (InvalidLanguageException $e) {
            return $this->errorUnprocessable($e->getMessage());
        }
        if ($response) {
            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'error'], 500);
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
            $result = app('api')->categories()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($result, new CategoryTransformer);
    }

    public function putProducts($id, Request $request)
    {
        try {
            $result = app('api')->categories()->updateProducts($id, $request->all());
        } catch (NotFoundException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithItem($result, new CategoryTransformer);
    }

    /**
     * Handles the request to delete a category.
     * @param  string        $id
     * @param  DeleteRequest $request
     * @return Json
     */
    public function destroy($id, Request $request)
    {
        try {
            $result = app('api')->categories()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
