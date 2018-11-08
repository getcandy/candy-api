<?php

namespace GetCandy\Api\Customers\Services;

use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Auth\Models\User;

class CustomerService extends BaseService
{
    public function __construct()
    {
        $model = config('auth.providers.users.model');
        $this->model = new $model;
    }

    /**
     * Registers a new customer
     * @param  array  $data
     * @return [type]       [description]
     */
    public function register(array $data)
    {
        $user = app('api')->users()->create($data);
        $user->assignRole('customer');
        $retail = app('api')->customerGroups()->getDefaultRecord();
        $user->groups()->sync([$retail->id]);
        return $user;
    }

    public function update($hashedId, array $data)
    {
        $user = app('api')->users()->getByHashedId($id);

        if (!empty($data['customer_groups'])) {
            $groups = app('api')->customerGroups()->getDecodedIds($data['customer_groups']);
//            dd($groups);
            $user->groups()->sync($groups);
        }

//        dd($user);
    }

    public function getPaginatedData($length = 50, $page = null, $keywords = null)
    {
        $query = $this->model;

        if ($keywords) {
            $query = $query->orWhere('email', 'LIKE', '%'.$keywords.'%')
                        ->orWhere('firstname', 'LIKE', '%'.$keywords.'%')
                        ->orWhere('lastname', 'LIKE', '%' . $keywords . '%');
        }

        return $query->paginate($length, ['*'], 'page', $page);
    }
}
