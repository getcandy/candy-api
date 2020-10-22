<?php

namespace GetCandy\Api\Core;

use GetCandy\Api\Core\Assets\Services\AssetService;
use GetCandy\Api\Core\Assets\Services\AssetSourceService;
use GetCandy\Api\Core\Assets\Services\AssetTransformService;
use GetCandy\Api\Core\Associations\Services\AssociationGroupService;
use GetCandy\Api\Core\Attributes\Services\AttributeGroupService;
use GetCandy\Api\Core\Attributes\Services\AttributeService;
use GetCandy\Api\Core\Auth\Services\RoleService;
use GetCandy\Api\Core\Baskets\Services\BasketLineService;
use GetCandy\Api\Core\Baskets\Services\BasketService;
use GetCandy\Api\Core\Baskets\Services\SavedBasketService;
use GetCandy\Api\Core\Categories\Services\CategoryService;
use GetCandy\Api\Core\Collections\Services\CollectionService;
use GetCandy\Api\Core\Currencies\Services\CurrencyService;
use GetCandy\Api\Core\Discounts\Services\DiscountService;
use GetCandy\Api\Core\Layouts\Services\LayoutService;
use GetCandy\Api\Core\Orders\Services\OrderService;
use GetCandy\Api\Core\Pages\Services\PageService;
use GetCandy\Api\Core\Payments\Services\PaymentService;
use GetCandy\Api\Core\Payments\Services\PaymentTypeService;
use GetCandy\Api\Core\Products\Services\ProductAssociationService;
use GetCandy\Api\Core\Products\Services\ProductCategoryService;
use GetCandy\Api\Core\Products\Services\ProductCollectionService;
use GetCandy\Api\Core\Products\Services\ProductService;
use GetCandy\Api\Core\Products\Services\ProductVariantService;
use GetCandy\Api\Core\Search\Services\SavedSearchService;
use GetCandy\Api\Core\Settings\Services\SettingService;
use GetCandy\Api\Core\Shipping\Services\ShippingMethodService;
use GetCandy\Api\Core\Shipping\Services\ShippingPriceService;
use GetCandy\Api\Core\Shipping\Services\ShippingZoneService;
use GetCandy\Api\Core\Tags\Services\TagService;
use GetCandy\Api\Core\Taxes\Services\TaxService;

class Factory
{
    /**
     * @var \GetCandy\Api\Core\Assets\Services\AssetService
     */
    protected $assets;

    /**
     * @var \GetCandy\Api\Core\Assets\Services\AssetSourceService
     */
    protected $assetSources;

    /**
     * @var \GetCandy\Api\Core\Associations\Services\AssociationGroupService
     */
    protected $associationGroups;

    /**
     * @var \GetCandy\Api\Core\Attributes\Services\AttributeService
     */
    protected $attributes;

    /**
     * @var \GetCandy\Api\Core\Attributes\Services\AttributeGroupService
     */
    protected $attributeGroups;

    /**
     * @var \GetCandy\Api\Core\Baskets\Services\BasketService
     */
    protected $baskets;

    /**
     * @var \GetCandy\Api\Core\Baskets\Services\SavedBasketService
     */
    protected $savedBaskets;

    /**
     * @var \GetCandy\Api\Core\Baskets\Services\BasketLineService
     */
    protected $basketLines;

    /**
     * @var \GetCandy\Api\Core\Categories\Services\CategoryService
     */
    protected $categories;

    /**
     * @var \GetCandy\Api\Core\Currencies\Services\CurrencyService
     */
    protected $currencies;

    /**
     * @var \GetCandy\Api\Core\Discounts\Services\DiscountService
     */
    protected $discounts;

    /**
     * @var \GetCandy\Api\Core\Layouts\Services\LayoutService
     */
    protected $layouts;

    /**
     * @var \GetCandy\Api\Core\Orders\Services\OrderService
     */
    protected $orders;

    /**
     * @var \GetCandy\Api\Core\Payments\Services\PaymentService
     */
    protected $payments;

    /**
     * @var \GetCandy\Api\Core\Payments\Services\PaymentTypeService
     */
    protected $paymentTypes;

    /**
     * @var \GetCandy\Api\Core\Pages\Services\PageService
     */
    protected $pages;

    /**
     * @var \GetCandy\Api\Core\Products\Services\ProductService
     */
    protected $products;

    /**
     * @var \GetCandy\Api\Core\Products\Services\ProductAssociationService
     */
    protected $productAssociations;

    /**
     * @var \GetCandy\Api\Core\Products\Services\ProductCollectionService
     */
    protected $productCollections;

    /**
     * @var \GetCandy\Api\Core\Products\Services\ProductVariantService
     */
    protected $productVariants;

    /**
     * @var \GetCandy\Api\Core\Auth\Services\RoleService
     */
    protected $roles;

    /**
     * @var \GetCandy\Api\Core\Search\Services\SavedSearchService
     */
    protected $savedSearch;
    /**
     * @var \GetCandy\Api\Core\Settings\Services\SettingService
     */
    protected $settings;

    /**
     * @var \GetCandy\Api\Core\Shipping\Services\ShippingMethodService
     */
    protected $shippingMethods;

    /**
     * @var \GetCandy\Api\Core\Shipping\Services\ShippingPriceService
     */
    protected $shippingPrices;

    /**
     * @var \GetCandy\Api\Core\Shipping\Services\ShippingZoneService
     */
    protected $shippingZones;

    /**
     * @var \GetCandy\Api\Core\Tags\Services\TagService
     */
    protected $tags;

    /**
     * @var \GetCandy\Api\Core\Taxes\Services\TaxService
     */
    protected $taxes;

    /**
     * @var \GetCandy\Api\Core\Assets\Services\AssetTransformService
     */
    protected $transforms;

    public function __construct(
        AssetService $assets,
        AssetSourceService $assetSources,
        AssetTransformService $transforms,
        AssociationGroupService $associationGroups,
        AttributeGroupService $attributeGroups,
        AttributeService $attributes,
        BasketLineService $basketLines,
        BasketService $baskets,
        CategoryService $categories,
        CollectionService $collections,
        CurrencyService $currencies,
        DiscountService $discounts,
        LayoutService $layouts,
        OrderService $orders,
        PaymentTypeService $paymentTypes,
        PaymentService $payments,
        PageService $pages,
        ProductAssociationService $productAssociations,
        ProductCategoryService $productCategories,
        ProductCollectionService $productCollections,
        ProductService $products,
        ProductVariantService $productVariants,
        RoleService $roles,
        SavedSearchService $savedSearch,
        SettingService $settings,
        SavedBasketService $savedBaskets,
        ShippingMethodService $shippingMethods,
        ShippingZoneService $shippingZones,
        ShippingPriceService $shippingPrices,
        TagService $tags,
        TaxService $taxes
    ) {
        $this->assetSources = $assetSources;
        $this->assets = $assets;
        $this->associationGroups = $associationGroups;
        $this->attributeGroups = $attributeGroups;
        $this->attributes = $attributes;
        $this->basketLines = $basketLines;
        $this->baskets = $baskets;
        $this->categories = $categories;
        $this->collections = $collections;
        $this->currencies = $currencies;
        $this->discounts = $discounts;
        $this->layouts = $layouts;
        $this->orders = $orders;
        $this->pages = $pages;
        $this->payments = $payments;
        $this->paymentTypes = $paymentTypes;
        $this->productAssociations = $productAssociations;
        $this->productCategories = $productCategories;
        $this->productCollections = $productCollections;
        $this->productVariants = $productVariants;
        $this->products = $products;
        $this->roles = $roles;
        $this->savedBaskets = $savedBaskets;
        $this->savedSearch = $savedSearch;
        $this->settings = $settings;
        $this->shippingMethods = $shippingMethods;
        $this->shippingZones = $shippingZones;
        $this->shippingPrices = $shippingPrices;
        $this->tags = $tags;
        $this->taxes = $taxes;
        $this->transforms = $transforms;
    }

    public function __call($name, $arguments)
    {
        if (! property_exists($this, $name)) {
            throw new \GetCandy\Api\Exceptions\InvalidServiceException(trans('exceptions.invalid_service', [
                'service' => $name,
            ]), 1);
        }

        return app()->make(
            get_class($this->{$name})
        );
    }
}
