<?php

namespace GetCandy\Api\Http\Controllers\Users;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Users\CreateRequest;
use GetCandy\Api\Http\Requests\Users\UpdateRequest;
use GetCandy\Api\Http\Transformers\Fractal\Users\UserTransformer;
use GetCandy\Api\Users\Contracts\UserContract;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    protected $users;

    public function __construct(UserContract $users)
    {
        $this->users = $users;
    }

    /**
     * Handles the request to show a listing of all users.
     *
     * @return Json
     */
    public function index(Request $request)
    {
        $paginator = app('api')->users()->getPaginatedData(
            $request->per_page,
            $request->page,
            $request->keywords,
            $request->ids
        );

        return $this->respondWithCollection($paginator, new UserTransformer());
    }

    /**
     * Handles the request to show a user based on their hashed ID.
     *
     * @param string $id
     *
     * @return Json
     */
    public function show($id)
    {
        $user = app('api')->users()->getByHashedId($id);

        if (!$user) {
            return $this->errorNotFound('Cannot find user');
        }

        return $this->respondWithItem($user, new UserTransformer());
    }

    /**
     * Handles the request to create a new user.
     *
     * @param CreateUserRequest $request
     *
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        $user = app('api')->users()->create($request->all());

        return $this->respondWithItem($user, new UserTransformer());
    }

    public function getCurrentUser(Request $request)
    {
        $user = $this->users->getByHashedId(
            $request->user()->encodedId()
        );

        return $this->respondWithItem($user, new UserTransformer());
    }

    public function update($userId, UpdateRequest $request)
    {
        $user = app('api')->users()->update($userId, $request->all());

        return $this->respondWithItem($user, new UserTransformer());
    }
}
