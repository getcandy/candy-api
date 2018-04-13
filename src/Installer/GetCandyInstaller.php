<?php

namespace GetCandy\Api\Installer;

use GetCandy\Api\Installer\Factories\TaxFactory;
use GetCandy\Api\Installer\Factories\UserFactory;
use GetCandy\Api\Installer\Factories\CurrencyFactory;
use GetCandy\Api\Installer\Factories\LanguageFactory;
use GetCandy\Api\Installer\Factories\ApiAttributeFactory;
use GetCandy\Api\Installer\Factories\CustomerGroupFactory;

class GetCandyInstaller
{
    protected $factories = [
        'attributes' => ApiAttributeFactory::class,
        'currencies' => CurrencyFactory::class,
        'customer_groups' => CustomerGroupFactory::class,
        'language' => LanguageFactory::class,
        'taxes' => TaxFactory::class,
        'users' => UserFactory::class,
    ];

    public function install($installer)
    {
        if (! isset($this->factories[$factory])) {
            return false;
        }

        return app()->make($this->factories[$installer])->init();
    }
}
