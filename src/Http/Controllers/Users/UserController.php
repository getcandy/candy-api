<?php

namespace GetCandy\Api\Http\Controllers\Users;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Core\Users\Contracts\UserContract;
use GetCandy\Api\Http\Requests\Users\CreateRequest;
use GetCandy\Api\Http\Requests\Users\UpdateRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Http\Transformers\Fractal\Users\UserTransformer;
use GetCandy\Api\Http\Resources\Users\UserResource;

class UserController extends BaseController
{
    protected $users;

    public function __construct(UserContract $users)
    {
        $this->users = $users;
    }

    /**
     * Handles the request to show a listing of all users.
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

        return $this->respondWithCollection($paginator, new UserTransformer);
    }

    /**
     * Handles the request to show a user based on their hashed ID.
     * @param  string $id
     * @return Json
     */
    public function show($id)
    {
        $user = app('api')->users()->getByHashedId($id);

        if (! $user) {
            return $this->errorNotFound('Cannot find user');
        }

        return $this->respondWithItem($user, new UserTransformer);
    }

    /**
     * Handles the request to create a new user.
     * @param  CreateUserRequest $request
     * @return Json
     */
    public function store(CreateRequest $request)
    {
        $user = app('api')->users()->create($request->all());

        return $this->respondWithItem($user, new UserTransformer);
    }

    public function getCurrentUser(Request $request)
    {
        $user = $this->users->getByHashedId(
            $request->user()->encodedId()
        );
        return $this->respondWithItem($user, new UserTransformer);
    }

    public function update($userId, UpdateRequest $request)
    {
        $user = app('api')->users()->update($userId, $request->all());

        return $this->respondWithItem($user, new UserTransformer);
    }

    public function deleteReusablePayment($id, Request $request)
    {
        try {
            $payment = app('api')->users()->getReusablePayment($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        if ($payment->user_id != $request->user()->id) {
            $this->errorUnauthorized();
        }
        app('api')->users()->deleteReusablePayment($payment);

        return $this->respondWithNoContent();
    }

    /**
     * Get the configured fields for a user
     *
     * @param Request $request
     * @return json
     */
    public function fields(Request $request)
    {
        return response()->json([
            'data' => [
                'fields' => config('getcandy.users.fields', []),
            ],
        ]);
    }
}
