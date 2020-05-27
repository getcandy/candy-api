<?php

namespace GetCandy\Api\Http\Controllers\Categories;

use Hashids;
use Drafting;
use Illuminate\Http\Request;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Core\Categories\CategoryCriteria;
use Intervention\Image\Exception\NotFoundException;
use GetCandy\Api\Http\Requests\Categories\CreateRequest;
use GetCandy\Api\Http\Requests\Categories\DeleteRequest;
use GetCandy\Api\Http\Requests\Categories\UpdateRequest;
use GetCandy\Api\Http\Requests\Categories\ReorderRequest;
use GetCandy\Api\Core\Categories\Services\CategoryService;
use GetCandy\Api\Http\Resources\Categories\CategoryResource;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryTransformer;

class CategoryController extends BaseController
{
    protected $service;

    public function __construct(CategoryService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request, CategoryCriteria $criteria)
    {

        $criteria
            ->tree($request->tree)
            ->depth($request->depth)
            ->include($request->include)
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
            ->with($request->include)
            ->withCount(['products', 'children']);

        return new CategoryCollection($query->get());
    }

    public function createDraft($id, Request $request)
    {
        $id = Hashids::connection('main')->decode($id);
        if (empty($id[0])) {
            return $this->errorNotFound();
        }
        $category = $this->service->findById($id[0], [], false);

        $draft = \Drafting::with('categories')->firstOrCreate($category);

        return new CategoryResource($draft->load($request->includes));
    }

    public function show($id, Request $request)
    {
        $id = (new Category)->decodeId($id);

        $includes = $request->include ?: [];

        if ($includes && is_string($includes)) {
            $includes = $this->parseIncludes($includes);
        }

        if (! $id) {
            return $this->errorNotFound();
        }

        $category = $this->service->findById($id, $includes, $request->draft);

        if (! $category) {
            return $this->errorNotFound();
        }

        $resource = new CategoryResource($category);
        $resource->only($this->parseIncludedFields($request));

        return $resource;
    }

    public function getNested()
    {
        $categories = app('api')->categories()->getNestedList();

        return $this->respondWithCollection($categories, new CategoryTransformer);
    }

    public function getByParent($parentID, Request $request)
    {
        $categories = app('api')->categories()->getByParentID(
            $parentID,
            $this->parseIncludes($request->include)
        );

        return new CategoryCollection($categories);
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

    public function publishDraft($id, Request $request)
    {
        $id = Hashids::connection('main')->decode($id);
        if (empty($id[0])) {
            return $this->errorNotFound();
        }
        $category = $this->service->findById($id[0], [], true);

        \DB::transaction(function () use ($category) {
            Drafting::with('categories')->publish($category);
        });

        $includes = $request->includes ? explode(',', $request->include) : [];

        return new CategoryResource($category->load($includes));
    }

    public function putChannels($id, Request $request)
    {
        try {
            $category = app('api')->categories()->updateChannels($id, $request->all());
        } catch (NotFoundException $e) {
            return $this->errorNotFound();
        }

        return new CategoryResource($category);
    }

    public function putCustomerGroups($id, Request $request)
    {
        try {
            $category = app('api')->categories()->updateCustomerGroups($id, $request->groups ?: []);
        } catch (NotFoundException $e) {
            return $this->errorNotFound();
        }

        return new CategoryResource($category);
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
