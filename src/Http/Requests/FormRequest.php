<?php

namespace GetCandy\Api\Http\Requests;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;
use GetCandy\Api\Exceptions\ValidationException;
use GetCandy\Api\Exceptions\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest as IlluminateFormRequest;

abstract class FormRequest extends IlluminateFormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \GetCandy\Api\Exceptions\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ValidationException($validator, $this->response(
            $validator->getMessageBag()->toArray()
        ));
    }

    /**
     * Get the proper failed validation response for the request.
     *
     * @param  array  $errors
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {
        return new JsonResponse($errors, 422);
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     *
     * @throws \GetCandy\Api\Exceptions\AuthorizationException
     */
    protected function failedAuthorization()
    {
        throw new AuthorizationException(trans('response.error.unauthorized'));
    }

    protected function prepareForValidation()
    {
        $data = $this->validationData();
        $this->replace($data);
    }
}
