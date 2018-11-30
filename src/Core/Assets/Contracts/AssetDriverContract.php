<?php

namespace GetCandy\Api\Core\Assets\Contracts;

interface AssetDriverContract
{
    public function process(array $data, $model);
}
