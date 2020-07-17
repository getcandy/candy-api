<?php

namespace GetCandy\Api\Http\Controllers\Auth;

use GetCandy;
use Illuminate\Support\Facades\Password;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use GetCandy\Api\Http\Requests\Auth\ForgotPasswordRequest;
use GetCandy\Api\Http\Resources\Auth\PasswordTokenResource;

class ForgotPasswordController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    public function __construct()
    {
        // $this->middleware('guest');
    }

    // public function broker()
    // {

    // }

    /**
     * Send a reset link to the given user.
     *
     * @param  \GetCandy\Api\Http\Requests\Auth\ForgotPasswordRequest  $request
     * @return array
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $token = $this->getPasswordResetToken($request->only('email'));

        return $token
                    ? $this->sendResetLinkResponse($token)
                    : $this->sendResetLinkFailedResponse($request, $token);
    }

    /**
     * Get a users reset token.
     *
     * @param  string  $email
     * @return string
     */
    protected function getPasswordResetToken($email)
    {
        $user = GetCandy::users()->getByEmail($email);

        if (! $user) {
            return false;
        }

        return Password::createToken($user);
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  string  $response
     * @return array
     */
    protected function sendResetLinkResponse($token)
    {
        return new PasswordTokenResource($token);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \GetCandy\Api\Http\Requests\Auth\ForgotPasswordRequest  $request
     * @param  mixed  $response
     * @return array
     */
    protected function sendResetLinkFailedResponse(ForgotPasswordRequest $request, $response)
    {
        return $this->respondWithSuccess('If an account with this email exists, an email has been sent with instructions');
    }
}
