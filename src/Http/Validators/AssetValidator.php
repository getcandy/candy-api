<?php

namespace GetCandy\Api\Http\Validators;

use GetCandy;

class AssetValidator
{
    public function validAssetUrl($attribute, $value, $parameters, $validator)
    {
        if (empty($parameters[0])) {
            return false;
        }

        $method = 'validate'.title_case($parameters[0]).'Url';
        if (method_exists($this, $method)) {
            return call_user_func([$this, $method], $value);
        }

        return false;
    }

    protected function validateYoutubeUrl($url)
    {
        $driver = GetCandy::assets()->getDriver('youtube');
        // \Log::debug($driver);
        return (bool) $driver->getInfo($url);
    }

    protected function validateVimeoUrl($url)
    {
        $driver = GetCandy::assets()->getDriver('vimeo');

        return $driver->getInfo($url);
    }

    protected function validateExternalUrl($url)
    {
        $driver = GetCandy::assets()->getDriver('external');

        return $driver->getInfo($url);
    }
}
