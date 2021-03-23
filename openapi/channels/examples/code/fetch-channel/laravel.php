use GetCandy\Api\Core\Channels\Actions\FetchChannel;

FetchChannel::run([
    'id' => 1, // Required without encoded_id or handle PHP_EOL
    'encoded_id' => '1AftLawd3d', // Required without id or handle
    'handle' => 'webstore' // Required if not using one of the above
]);