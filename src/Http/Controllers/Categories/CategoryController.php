<?php

namespace GetCandy\Api\Http\Controllers\Categories;

use Drafting;
use GetCandy;
use GetCandy\Api\Core\Categories\CategoryCriteria;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Categories\Services\CategoryService;
use GetCandy\Api\Exceptions\MinimumRecordRequiredException;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Categories\CreateRequest;
use GetCandy\Api\Http\Requests\Categories\ReorderRequest;
use GetCandy\Api\Http\Requests\Categories\UpdateRequest;
use GetCandy\Api\Http\Resources\Categories\CategoryCollection;
use GetCandy\Api\Http\Resources\Categories\CategoryResource;
use Hashids;
use Illuminate\Http\Request;
use Intervention\Image\Exception\NotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends BaseController
{
    /**
     * @var \GetCandy\Api\Core\Categories\Services\CategoryService
     */
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
            ->include($this->parseIncludes($request->include))
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
            ->with($this->parseIncludes($request->include))
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

        if (! $category) {
            return $this->errorNotFound();
        }

        $draft = \Drafting::with('categories')->firstOrCreate($category);

        return new CategoryResource($draft->load($this->parseIncludes($request->include)));
    }

    public function show($id, Request $request)
    {
        $id = (new Category)->decodeId($id);

        if (! $id) {
            return $this->errorNotFound();
        }

        $category = $this->service->findById($id, $this->parseIncludes($request->include), $request->draft);

        if (! $category) {
            return $this->errorNotFound();
        }

        $resource = new CategoryResource($category);
        $resource->only($this->parseIncludedFields($request));

        return $resource;
    }

    public function getNested()
    {
        return new CategoryCollection(
            GetCandy::categories()->getNestedList()
        );
    }

    public function getByParent($parentID, Request $request)
    {
        $categories = GetCandy::categories()->getByParentID(
            $parentID,
            $this->parseIncludes($request->include)
        );

        return new CategoryCollection($categories);
    }

    /**
     * Create new category from basic information.
     *
     * @param  \GetCandy\Api\Http\Requests\Categories\CreateRequest  $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function store(CreateRequest $request)
    {
        try {
            $response = GetCandy::categories()->create($request->all());
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
            return new CategoryResource($response);
        }

        return response()->json('Error', 500);
    }

    /**
     * Handles the request to reorder the categories.
     *
     * @param  \GetCandy\Api\Http\Requests\Categories\ReorderRequest  $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function reorder(ReorderRequest $request)
    {
        try {
            $response = GetCandy::categories()->reorder($request->all());
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
     * Handles the request to update a category.
     *
     * @param  string  $id
     * @param  \GetCandy\Api\Http\Requests\Categories\UpdateRequest  $request
     * @return array
     */
    public function update($id, UpdateRequest $request)
    {
        try {
            $category = GetCandy::categories()->update($id, $request->all());
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return new CategoryResource($category);
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

        return new CategoryResource($category->load($this->parseIncludes($request->include)));
    }

    public function putChannels($id, Request $request)
    {
        try {
            $category = GetCandy::categories()->updateChannels($id, $request->all());
        } catch (NotFoundException $e) {
            return $this->errorNotFound();
        }

        return new CategoryResource($category);
    }

    public function putCustomerGroups($id, Request $request)
    {
        try {
            $category = GetCandy::categories()->updateCustomerGroups($id, $request->groups ?: []);
        } catch (NotFoundException $e) {
            return $this->errorNotFound();
        }

        return new CategoryResource($category);
    }

    public function putProducts($id, Request $request)
    {
        try {
            $category = GetCandy::categories()->updateProducts($id, $request->all());
        } catch (NotFoundException $e) {
            return $this->errorNotFound();
        }

        return new CategoryResource($category);
    }

    /**
     * Handles the request to delete a category.
     *
     * @param  string  $id
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        try {
            GetCandy::categories()->delete($id);
        } catch (NotFoundHttpException $e) {
            return $this->errorNotFound();
        }

        return $this->respondWithNoContent();
    }
}
