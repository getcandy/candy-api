<?php

namespace GetCandy\Api\Http\Controllers\Auth;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Auth\ResetAccountPasswordRequest;
use GetCandy\Api\Core\Users\Actions\ResetPassword;

class AccountController extends BaseController
{
    public function resetPassword(ResetAccountPasswordRequest $request)
    {
        //$result = GetCandy::users()->resetPassword($request->current_password, $request->password, $request->user());
        $result = ResetPassword::run([
            'current_password' => $request->current_password,
            'new_password' => $request->password,
            'user' => $request->user(),
        ]);

        if (! $result) {
            return $this->errorForbidden();
        }

        return $this->respondWithSuccess('Password changed');
    }
}
