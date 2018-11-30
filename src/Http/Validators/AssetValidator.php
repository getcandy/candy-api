<?php

namespace GetCandy\Api\Http\Validators;

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
        $driver = app('api')->assets()->getDriver('youtube');

        return (bool) $driver->getInfo($url);
    }

    protected function validateVimeoUrl($url)
    {
        $driver = app('api')->assets()->getDriver('vimeo');

        return $driver->getInfo($url);
    }

    protected function validateExternalUrl($url)
    {
        $driver = app('api')->assets()->getDriver('external');

        return $driver->getInfo($url);
    }
}
