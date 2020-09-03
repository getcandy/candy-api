<?php

namespace GetCandy\Api\Core\Traits;

use Illuminate\Http\Response;

trait ReturnsJsonResponses
{
    protected $statusCode = 200;

    /**
     * Gets the current status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Sets the status code for the getcandy::response.
     *
     * @param  int  $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * Generates a Response with a 403 HTTP header and a given message.
     *
     * @param  string|null  $message
     * @return array
     */
    public function errorForbidden($message = null)
    {
        return $this->setStatusCode(403)->respondWithError(($message ?: trans('getcandy::response.error.forbidden')));
    }

    /**
     * Generates a response with a 410 HTTP header and a given message.
     *
     * @param  string|null  $message
     * @return array
     */
    public function errorExpired($message = null)
    {
        return $this->setStatusCode(410)->respondWithError(($message ?: trans('getcandy::response.error.expired')));
    }

    /**
     * Generates a Response with a 500 HTTP header and a given message.
     *
     * @param  string|null  $message
     * @return array
     */
    public function errorInternalError($message = null)
    {
        return $this->setStatusCode(500)->respondWithError(($message ?: trans('getcandy::response.error.internal')));
    }

    /**
     * Generates a Response with a 401 HTTP header and a given message.
     *
     * @param  string|null  $message
     * @return array
     */
    public function errorUnauthorized($message = null)
    {
        return $this->setStatusCode(401)->respondWithError(
            $message ?: trans('getcandy::response.error.unauthorized')
        );
    }

    /**
     * Generates a Response with a 400 HTTP header and a given message.
     *
     * @param  string|null  $message
     * @return array
     */
    public function errorWrongArgs($message = null)
    {
        return $this->setStatusCode(400)->respondWithError(
            $message ?: trans('getcandy::response.error.wrong_args')
        );
    }

    /**
     * Generates a Response with a 404 HTTP header and a given message.
     *
     * @param  string|null  $message
     * @return array
     */
    public function errorNotFound($message = null)
    {
        return $this->setStatusCode(404)->respondWithError(
            $message ?: trans('getcandy::response.error.not_found')
        );
    }

    public function errorUnprocessable($data)
    {
        return $this->setStatusCode(422)->respondWithError($data);
    }

    public function respondWithNoContent()
    {
        return response()->json(null, 204);
    }

    public function respondWithSuccess($message = null)
    {
        return $this->respondWithArray([
            'success' => [
                'http_code' => $this->statusCode,
                'message' => $message,
            ],
        ]);
    }

    public function respondWithComplete($status = 201)
    {
        return $this->setStatusCode($status)->respondWithArray(['processed' => true]);
    }

    /**
     * Returns an error getcandy::response.
     *
     * @param  string|null  $message
     * @return array
     */
    protected function respondWithError($message = null)
    {
        if ($this->statusCode == 200) {
            trigger_error(trans('getcandy::response.error.200'));
        }

        return $this->respondWithArray([
            'error' => [
                'http_code' => $this->statusCode,
                'message' => $message,
            ],
        ]);
    }

    /**
     * Builds a response array.
     *
     * @param  array  $array - The array of data
     * @param  array  $headers - Any headers to attach to the response
     * @return array
     */
    protected function respondWithArray(array $array, array $headers = [])
    {
        return response()->json($array, $this->statusCode)->withHeaders($headers);
    }
}
