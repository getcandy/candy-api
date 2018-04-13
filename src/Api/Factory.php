<?php

namespace GetCandy\Api;

use GetCandy\Api\Tags\Services\TagService;
use GetCandy\Api\Auth\Services\RoleService;
use GetCandy\Api\Taxes\Services\TaxService;
use GetCandy\Api\Pages\Services\PageService;
use GetCandy\Api\Users\Services\UserService;
use GetCandy\Api\Assets\Services\AssetService;
use GetCandy\Api\Orders\Services\OrderService;
use GetCandy\Api\Routes\Services\RouteService;
use GetCandy\Api\Search\Services\SearchService;
use GetCandy\Api\Baskets\Services\BasketService;
use GetCandy\Api\Layouts\Services\LayoutService;
use GetCandy\Api\Channels\Services\ChannelService;
use GetCandy\Api\Payments\Services\PaymentService;
use GetCandy\Api\Products\Services\ProductService;
use GetCandy\Api\Settings\Services\SettingService;
use GetCandy\Api\Addresses\Services\AddressService;
use GetCandy\Api\Countries\Services\CountryService;
use GetCandy\Api\Assets\Services\AssetSourceService;
use GetCandy\Api\Baskets\Services\BasketLineService;
use GetCandy\Api\Customers\Services\CustomerService;
use GetCandy\Api\Discounts\Services\DiscountService;
use GetCandy\Api\Languages\Services\LanguageService;
use GetCandy\Api\Search\Services\SavedSearchService;
use GetCandy\Api\Categories\Services\CategoryService;
use GetCandy\Api\Currencies\Services\CurrencyService;
use GetCandy\Api\Attributes\Services\AttributeService;
use GetCandy\Api\Payments\Services\PaymentTypeService;
use GetCandy\Api\Assets\Services\AssetTransformService;
use GetCandy\Api\Shipping\Services\ShippingZoneService;
use GetCandy\Api\Collections\Services\CollectionService;
use GetCandy\Api\Products\Services\ProductFamilyService;
use GetCandy\Api\Shipping\Services\ShippingPriceService;
use GetCandy\Api\Customers\Services\CustomerGroupService;
use GetCandy\Api\Products\Services\ProductVariantService;
use GetCandy\Api\Shipping\Services\ShippingMethodService;
use GetCandy\Api\Products\Services\ProductCategoryService;
use GetCandy\Api\Attributes\Services\AttributeGroupService;
use GetCandy\Api\Products\Services\ProductCollectionService;
use GetCandy\Api\Products\Services\ProductAssociationService;
use GetCandy\Api\Associations\Services\AssociationGroupService;

class Factory
{
    /**
     * @var AddressService
     */
    protected $addresses;

    /**
     * @var AssetService
     */
    protected $assets;

    /**
     * @var \GetCandy\Api\Assets\Services\AssetSourceService
     */
    protected $assetSources;

    /**
     * @var \GetCandy\Api\Associations\Services\AssociationGroupService
     */
    protected $associationGroups;

    /**
     * @var AttributeService
     */
    protected $attributes;

    /**
     * @var AttributeGroupService
     */
    protected $attributeGroups;

    /**
     * @var BasketService
     */
    protected $baskets;

    /**
     * @var BasketLineService
     */
    protected $basketLines;

    /**
     * @var CategoryService
     */
    protected $categories;

    /**
     * @var ChannelService
     */
    protected $channels;

    /**
     * @var CountryService
     */
    protected $countries;

    /**
     * @var CurrencyService
     */
    protected $currencies;

    /**
     * @var CustomerService
     */
    protected $customers;

    /**
     * @var DiscountService
     */
    protected $discounts;

    /**
     * @var LayoutService
     */
    protected $layouts;

    /**
     * @var LanguageService
     */
    protected $languages;

    /**
     * @var OrderService
     */
    protected $orders;

    /**
     * @var PaymentService
     */
    protected $payments;

    /**
     * @var PaymentTypeService
     */
    protected $paymentTypes;

    /**
     * @var PageService
     */
    protected $pages;

    /**
     * @var ProductService
     */
    protected $products;

    /**
     * @var ProductAssociationService
     */
    protected $productAssociations;

    /**
     * @var ProductFamilyService
     */
    protected $productCollections;

    /**
     * @var ProductFamilyService
     */
    protected $productFamilies;

    /**
     * @var ProductVariantService
     */
    protected $productVariants;

    /**
     * @var RouteService
     */
    protected $routes;

    /**
     * @var RoleService
     */
    protected $roles;

    /**
     * @var SavedSearchService
     */
    protected $savedSearch;

    /**
     * @var \GetCandy\Api\Services\SearchService;
     */
    protected $search;

    /**
     * @var \GetCandy\Api\SettingService
     */
    protected $settings;

    /**
     * @var ShippingMethodService
     */
    protected $shippingMethods;

    /**
     * @var ShippingPriceService
     */
    protected $shippingPrices;

    /**
     * @var ShippingZoneService
     */
    protected $shippingZones;

    /**
     * @var TagService
     */
    protected $tags;

    /**
     * @var TaxService
     */
    protected $taxes;

    /**
     * @var \GetCandy\Api\Assets\Services\AssetTransformService
     */
    protected $transforms;

    /**
     * @var UserService
     */
    protected $users;

    public function __construct(
        AddressService $addresses,
        AssetService $assets,
        AssetSourceService $assetSources,
        AssetTransformService $transforms,
        AssociationGroupService $associationGroups,
        AttributeGroupService $attributeGroups,
        AttributeService $attributes,
        BasketLineService $basketLines,
        BasketService $baskets,
        CategoryService $categories,
        ChannelService $channels,
        CollectionService $collections,
        CurrencyService $currencies,
        CountryService $countries,
        CustomerGroupService $customerGroups,
        CustomerService $customers,
        DiscountService $discounts,
        LanguageService $languages,
        LayoutService $layouts,
        OrderService $orders,
        PaymentTypeService $paymentTypes,
        PaymentService $payments,
        PageService $pages,
        ProductAssociationService $productAssociations,
        ProductCategoryService $productCategories,
        ProductCollectionService $productCollections,
        ProductFamilyService $productFamilies,
        ProductService $products,
        ProductVariantService $productVariants,
        RoleService $roles,
        RouteService $routes,
        SavedSearchService $savedSearch,
        SearchService $search,
        SettingService $settings,
        ShippingMethodService $shippingMethods,
        ShippingZoneService $shippingZones,
        ShippingPriceService $shippingPrices,
        TagService $tags,
        TaxService $taxes,
        UserService $users
    ) {
        $this->addresses = $addresses;
        $this->assetSources = $assetSources;
        $this->assets = $assets;
        $this->associationGroups = $associationGroups;
        $this->attributeGroups = $attributeGroups;
        $this->attributes = $attributes;
        $this->basketLines = $basketLines;
        $this->baskets = $baskets;
        $this->categories = $categories;
        $this->channels = $channels;
        $this->collections = $collections;
        $this->countries = $countries;
        $this->currencies = $currencies;
        $this->customerGroups = $customerGroups;
        $this->customers = $customers;
        $this->discounts = $discounts;
        $this->languages = $languages;
        $this->layouts = $layouts;
        $this->orders = $orders;
        $this->pages = $pages;
        $this->payments = $payments;
        $this->paymentTypes = $paymentTypes;
        $this->productAssociations = $productAssociations;
        $this->productCategories = $productCategories;
        $this->productCollections = $productCollections;
        $this->productFamilies = $productFamilies;
        $this->productVariants = $productVariants;
        $this->products = $products;
        $this->roles = $roles;
        $this->routes = $routes;
        $this->savedSearch = $savedSearch;
        $this->search = $search;
        $this->settings = $settings;
        $this->shippingMethods = $shippingMethods;
        $this->shippingZones = $shippingZones;
        $this->shippingPrices = $shippingPrices;
        $this->tags = $tags;
        $this->taxes = $taxes;
        $this->transforms = $transforms;
        $this->users = $users;
    }

    public function __call($name, $arguments)
    {
        if (! property_exists($this, $name)) {
            throw new \GetCandy\Exceptions\InvalidServiceException(trans('exceptions.invalid_service', [
                'service' => $name,
            ]), 1);
        }

        return app()->make(
            get_class($this->{$name})
        );
        // return ;
    }
}
