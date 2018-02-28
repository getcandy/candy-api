<?php

namespace GetCandy\Api\Assets\Contracts;

interface AssetDriverContract
{
    public function process(array $data, $model);
}