<?php

namespace Tests\Unit\Shipping\Factories;


use Tests\TestCase;
use GetCandy\Api\Core\Scaffold\AliasResolver;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Exceptions\AliasResolutionException;

/**
 * @group scaffold
 */
class AliasResolverTest extends TestCase
{
    public function test_can_resolve_default_class_names()
    {
        $this->assertEquals(Product::class, AliasResolver::resolve('product'));
        $this->assertEquals(Category::class, AliasResolver::resolve('category'));
    }

    public function test_can_add_additional_classes_to_be_resolved()
    {
        AliasResolver::addAliases('language', Language::class);
        $this->assertEquals(Language::class, AliasResolver::resolve('language'));
    }

    public function test_fully_qualified_classnames_with_resolve_themselves()
    {
        $this->assertEquals(Product::class, Product::class);
    }

    public function test_exception_is_thrown_when_unable_to_resolve_alias()
    {
        $this->expectException(AliasResolutionException::class);
        AliasResolver::resolve('foobar');
    }
}
