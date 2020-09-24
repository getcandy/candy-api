<?php

namespace GetCandy\Api\Http\Controllers\Auth;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Auth\ImpersonateRequest;

class ImpersonateController extends BaseController
{
    public function process(ImpersonateRequest $request)
    {
        $token = GetCandy::users()->getImpersonationToken($request->customer_id);

        return response()->json([
            'access_token' => $token->accessToken,
        ]);
    }
}
