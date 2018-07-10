<?php

namespace GetCandy\Api\Http\Controllers\Auth;

use Illuminate\Support\Facades\Password;
use GetCandy\Api\Http\Controllers\BaseController;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use GetCandy\Api\Http\Requests\Auth\ForgotPasswordRequest;
use GetCandy\Api\Http\Transformers\Fractal\Auth\PasswordTokenTransformer;

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
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
     * @param string $email
     * @return void
     */
    protected function getPasswordResetToken($email)
    {
        $user = app('api')->users()->getByEmail($email);

        if (! $user) {
            return false;
        }

        return Password::createToken($user);
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse($token)
    {
        return $this->respondWithItem($token, new PasswordTokenTransformer);
    }

    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(ForgotPasswordRequest $request, $response)
    {
        return $this->respondWithSuccess('If an account with this email exists, an email has been sent with instructions');
    }
}
