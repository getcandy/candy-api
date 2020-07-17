<?php

namespace GetCandy\Api\Http\Controllers\Users;

use GetCandy;
use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Core\Users\Contracts\UserContract;
use GetCandy\Api\Http\Requests\Users\CreateRequest;
use GetCandy\Api\Http\Requests\Users\UpdateRequest;
use GetCandy\Api\Http\Resources\Users\UserResource;
use GetCandy\Api\Http\Resources\Users\UserCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends BaseController
{
    /**
     * @var \GetCandy\Api\Core\Users\Contracts\UserContract
     */
    protected $users;

    public function __construct(UserContract $users)
    {
        $this->users = $users;
    }

    /**
     * Handles the request to show a listing of all users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index(Request $request)
    {
        $paginator = GetCandy::users()->getPaginatedData(
            $request->per_page,
            $request->page,
            $request->keywords,
            $request->ids
        );
        return new UserCollection($paginator);
    }

    /**
     * Handles the request to show a user based on their hashed ID.
     *
     * @param  string  $id
     * @return array
     */
    public function show($id)
    {
        $user = GetCandy::users()->getByHashedId($id);

        if (! $user) {
            return $this->errorNotFound('Cannot find user');
        }
        return new UserResource($user);
    }

    /**
     * Handles the request to create a new user.
     *
     * @param  \GetCandy\Api\Http\Requests\Users\CreateRequest  $request
     * @return array
     */
    public function store(CreateRequest $request)
    {
        return new UserResource(
            GetCandy::users()->create($request->all())
        );
    }

    public function getCurrentUser(Request $request)
    {
        $user = $this->users->getByHashedId(
            $request->user()->encodedId()
        );

        return new UserResource(
            $request->user()->load([
                'addresses', 'roles.permissions', 'details',
            ])
        );
        return new UserResource($user);
    }

    public function update($userId, UpdateRequest $request)
    {
        return new UserResource(
            GetCandy::users()->update($userId, $request->all())
        );
    }

    public function deleteReusablePayment($id, Request $request)
    {
        try {
            $payment = GetCandy::users()->getReusablePayment($id);
        } catch (ModelNotFoundException $e) {
            return $this->errorNotFound();
        }

        if ($payment->user_id != $request->user()->id) {
            $this->errorUnauthorized();
        }
        GetCandy::users()->deleteReusablePayment($payment);

        return $this->respondWithNoContent();
    }

    /**
     * Get the configured fields for a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
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
