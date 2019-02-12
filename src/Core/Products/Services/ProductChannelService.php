<?php

namespace GetCandy\Api\Core\Products\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Channels\Services\ChannelService;

class ProductChannelService extends BaseService
{
    protected $channelService;

    public function __construct(ChannelService $channels)
    {
        $this->model = new Product;
        $this->channelService = $channels;
    }

    /**
     * Stores a product association.
     * @param  string $product
     * @param  array $data
     * @return mixed
     */
    public function store($product, $channels)
    {
        $product = $this->getByHashedId($product);
        $product->channels()->sync(
            $this->getChannelMapping($channels)
        );
        $product->load('channels');

        return $product;
    }

    /**
     * Destroys product customer groups.
     * @param  string $product
     * @return bool
     */
    public function destroy($product)
    {
        $product = $this->getByHashedId($product);
        $product->customerGroups()->detach();

        return $product;
    }
}
