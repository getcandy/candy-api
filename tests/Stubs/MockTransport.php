<?php

namespace Tests\Stubs;

use Elastica\JSON;
use Elastica\Request;
use Elastica\Response;
use Elastica\Transport\NullTransport;

// Declaration of Tests\Stubs\MockTransport::_getGuzzleClient($baseUrl, $persistent = true) must be compatible with Elastica\Transpo
//   rt\Guzzle::_getGuzzleClient(bool $persistent = true): GuzzleHttp\Client


class MockTransport extends NullTransport
{
    /**
     * Generate an example response object.
     *
     * @param array $params Hostname, port, path, ...
     *
     * @return Response $response
     */
    public function generateDefaultResponse(array $params): Response
    {
        $response = [
            'took' => 0,
            'timed_out' => false,
            'indices' => [],
            '_shards' => [
                'total' => 0,
                'successful' => 0,
                'failed' => 0,
            ],
            'hits' => [
                'total' => [
                    'value' => 0,
                ],
                'max_score' => null,
                'hits' => [],
            ],
            'params' => $params,
        ];

        return new Response(JSON::stringify($response));
    }
}