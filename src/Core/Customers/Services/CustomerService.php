<?php

namespace GetCandy\Api\Core\Customers\Services;

use GetCandy\Api\Core\Scaffold\BaseService;

class CustomerService extends BaseService
{
    public function __construct()
    {
        $model = config('auth.providers.users.model');
        $this->model = new $model;
    }

    /**
     * Registers a new customer.
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

        if (! empty($data['customer_groups'])) {
            $groups = app('api')->customerGroups()->getDecodedIds($data['customer_groups']);
            $user->groups()->sync($groups);
        }

        dd($user);
    }

    public function getPaginatedData($length = 50, $page = null, $keywords = null)
    {
        $query = $this->model;

        if ($keywords) {
            $segments = explode(' ', $keywords);

            $query = $query
                ->whereHas('details', function ($q) use ($segments, $keywords) {
                    if (count($segments) > 1) {
                        $q->where('firstname', '=', $segments[0])
                            ->where('lastname', '=', $segments[1])
                            ->orWhere('company_name', 'LIKE', '%'.$keywords.'%');
                    } else {
                        $q->where('firstname', 'LIKE', '%'.$keywords.'%')
                            ->orWhere('lastname', 'LIKE', '%'.$keywords.'%')
                            ->orWhere('company_name', 'LIKE', '%'.$keywords.'%');
                    }
                })->orWhere('email', 'LIKE', '%'.$keywords.'%');
        }

        return $query->paginate($length, ['*'], 'page', $page);
    }
}
