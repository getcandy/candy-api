<?php

namespace GetCandy\Api\Core\Customers\Services;

use GetCandy;
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
     *
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function register(array $data)
    {
        $user = GetCandy::users()->create($data);
        $user->assignRole('customer');
        $retail = GetCandy::customerGroups()->getDefaultRecord();
        $user->groups()->sync([$retail->id]);

        return $user;
    }

    /**
     * Returns model by a given hashed id.
     *
     * @param  string  $id
     * @param  array  $includes
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getByHashedId($id, $includes = [])
    {
        $id = $this->model->decodeId($id);

        return $this->model->with($includes)->findOrFail($id);
    }

    public function update($hashedId, array $data)
    {
        $user = GetCandy::users()->getByHashedId($id);

        if (! empty($data['customer_groups'])) {
            $groups = GetCandy::customerGroups()->getDecodedIds($data['customer_groups']);
            $user->groups()->sync($groups);
        }

        dd($user);
    }

    public function getPaginatedData($length = 50, $page = null, $keywords = null, $includes = [])
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

        return $query->with($includes)->paginate($length, ['*'], 'page', $page);
    }
}
