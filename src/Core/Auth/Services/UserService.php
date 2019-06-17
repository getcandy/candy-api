<?php

namespace GetCandy\Api\Core\Auth\Services;

use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Scaffold\BaseService;

class UserService extends BaseService
{
    public function __construct()
    {
        $this->model = new User();
    }

    public function getCustomerGroups($user = null)
    {
        return \GetCandy::getGroups();
    }

    public function getByEmail($email)
    {
        return $this->model->where('email', '=', $email)->first();
    }

    /**
     * Gets paginated data for the record.
     * @param  int $length How many results per page
     * @param  int  $page   The page to start
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedData($length = 50, $page = null, $keywords = null, $ids = [])
    {
        $query = $this->model;
        if ($keywords) {
            $query = $query
                ->where('firstname', 'LIKE', '%'.$keywords.'%')
                ->orWhere('lastname', 'LIKE', '%'.$keywords.'%')
                ->orWhere('company_name', 'LIKE', '%'.$keywords.'%')
                ->orWhere('email', 'LIKE', '%'.$keywords.'%');
        }

        if (count($ids)) {
            $realIds = $this->getDecodedIds($ids);
            $query = $query->whereIn('id', $realIds);
        }

        return $query->paginate($length, ['*'], 'page', $page);
    }

    public function update($userId, array $data)
    {
        $user = $this->getByHashedId($userId);

        $user->email = $data['email'];

        if (! empty($data['firstname'])) {
            $user->firstname = $data['firstname'];
        }

        if (! empty($data['lastname'])) {
            $user->lastname = $data['lastname'];
        }

        if (! empty($data['title'])) {
            $user->title = $data['title'];
        }

        if (isset($data['contact_number'])) {
            $user->contact_number = $data['contact_number'];
        } else {
            $user->contact_number = null;
        }

        if (isset($data['company_name'])) {
            $user->company_name = $data['company_name'];
        } else {
            $user->company_name = null;
        }

        if (isset($data['vat_no'])) {
            $user->vat_no = $data['vat_no'];
        } else {
            $user->vat_no = null;
        }

        if (! empty($data['password'])) {
            $user->password = bcrypt($data['password']);
        }

        if (! empty($data['customer_groups'])) {
            $groupData = app('api')->customerGroups()->getDecodedIds($data['customer_groups']);
            $user->groups()->sync($groupData);
        } else {
            $default = app('api')->customerGroups()->getDefaultRecord();
            $user->groups()->attach($default);
        }

        $user->save();

        return $user;
    }

    public function resetPassword($old, $new, $user)
    {
        if (! \Hash::check($old, $user->password)) {
            return false;
        }

        $user->password = bcrypt($new);
        $user->save();

        return $user;
    }

    /**
     * Creates a user token.
     *
     * @param string $userId
     *
     * @return PersonalAccessTokenResult
     */
    public function getImpersonationToken($userId)
    {
        $user = $this->getByHashedId($userId);

        return $user->createToken(str_random(25));
    }
}
