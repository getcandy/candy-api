<?php

namespace GetCandy\Api\Http\Controllers\Auth;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Reset the given user's password.
     *
     * @param  \GetCandy\Api\Http\Requests\Auth\ResetPasswordRequest  $request
     * @return array
     */
    public function reset(ResetPasswordRequest $request)
    {
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
                    ? $this->sendResetResponse($response)
                    : $this->sendResetFailedResponse($request, $response);
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  string  $response
     * @return array
     */
    protected function sendResetResponse($response)
    {
        return $this->respondWithSuccess(trans($response));
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param  \GetCandy\Api\Http\Requests\Auth\ResetPasswordRequest  $request
     * @param  string  $response
     * @return array
     */
    protected function sendResetFailedResponse(ResetPasswordRequest $request, $response)
    {
        return $this->errorUnprocessable(['email' => trans($response)]);
    }
}
