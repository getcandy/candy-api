<?php

namespace GetCandy\Api\Core\ReusablePayments\Policies;

use GetCandy\Api\Core\ReusablePayments\Models\ReusablePayment;
use Illuminate\Foundation\Auth\User;

class ReusablePaymentPolicy
{
    /**
     * Determine if the user can create a reusable payment.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return true;
    }

    /**
     * Determine if the user can update a reusable payment.
     *
     * @param User $user
     * @param ReusablePayment $reusablePayment
     * @return bool
     */
    public function update(User $user, ReusablePayment $reusablePayment)
    {
        return $user->can('manage-reusable-payments') || $user->id === $reusablePayment->user_id;
    }

    /**
     * Determine if the user can view a reusable payment.
     *
     * @param User $user
     * @param ReusablePayment $reusablePayment
     * @return bool
     */
    public function view(User $user, ReusablePayment $reusablePayment)
    {
        return $this->update($user, $reusablePayment);
    }

    /**
     * Determine if the user can delete a reusable payment.
     *
     * @param User $user
     * @param ReusablePayment $reusablePayment
     * @return bool
     */
    public function delete(User $user, ReusablePayment $reusablePayment)
    {
        return $this->update($user, $reusablePayment);
    }
}
