<?php

namespace GetCandy\Api\Core\Users\Services;

use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Users\Contracts\UserContract;
use GetCandy\Api\Core\Payments\Models\ReusablePayment;

class UserService extends BaseService implements UserContract
{
    public function __construct()
    {
        $model = config('auth.providers.users.model');
        $this->model = new $model;
    }

    public function getCustomerGroups($user = null)
    {
        return \GetCandy::getGroups();
    }

    /**
     * Returns model by a given hashed id.
     * @param  string $id
     * @throws  Illuminate\Database\Eloquent\ModelNotFoundException
     * @return Illuminate\Database\Eloquent\Model
     */
    public function getByHashedId($id)
    {
        $id = $this->model->decodeId($id);

        return $this->model->with('details')->findOrFail($id);
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
        $query = $this->model->with(['details']);
        if ($keywords) {
            $keywords = explode(' ', $keywords);
            foreach ($keywords as $keyword) {
                $query = $query->whereHas('details', function ($q) use ($keyword) {
                    $q->where('firstname', 'LIKE', '%'.$keyword.'%')
                        ->orWhere('lastname', 'LIKE', '%'.$keyword.'%')
                        ->orWhere('company_name', 'LIKE', '%'.$keyword.'%')
                        ->orWhere('email', 'LIKE', '%'.$keyword.'%');
                });
            }
        }

        if (! empty($ids)) {
            $realIds = $this->getDecodedIds($ids);
            $query = $query->whereIn('id', $realIds);
        }

        return $query->paginate($length, ['*'], 'page', $page);
    }

    /**
     * Creates a resource from the given data.
     *
     * @param  array  $data
     *
     * @return GetCandy\Api\Core\Auth\Models\User
     */
    public function create($data)
    {
        $user = $this->model;

        $user->name = $data['firstname'].' '.$data['lastname'];
        $user->email = $data['email'];
        $user->password = bcrypt($data['password']);

        // $user->title = $data['title'];

        if (empty($data['language'])) {
            $lang = app('api')->languages()->getDefaultRecord();
        } else {
            $lang = app('api')->languages()->getEnabledByLang($data['language']);
        }

        $user->language()->associate($lang);

        $user->save();

        $data['details']['firstname'] = $data['firstname'];
        $data['details']['lastname'] = $data['lastname'];
        $data['details']['fields'] = $data['fields'] ?? [];

        if (! empty($data['details'])) {
            $data['details']['user_id'] = $user->id;
            $user->details()->create($data['details']);
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

    /**
     * Get a reusable payment by it's id.
     *
     * @param string $id
     * @return ReusablePayment
     */
    public function getReusablePayment($id)
    {
        $realId = (new ReusablePayment)->decodeId($id);

        return ReusablePayment::findOrFail($realId);
    }

    /**
     * Delete a reusable payment.
     *
     * @param ReusablePayment $payment
     * @return bool
     */
    public function deleteReusablePayment($payment)
    {
        return $payment->delete();
    }

    public function update($userId, array $data)
    {
        $user = $this->getByHashedId($userId);

        $user->email = $data['email'];

        if (! empty($data['details'])) {
            $details = $user->details;
            if (! $details) {
                $user->details()->create($data['details']);
            } else {
                $details->fill($data['details']);
                $details->save();
            }
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
