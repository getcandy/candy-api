<?php

namespace GetCandy\Api\Core\Products\Factories;

use DB;
use Storage;
use Illuminate\Support\Collection;
use League\Flysystem\FileNotFoundException;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;
use GetCandy\Api\Core\Products\Interfaces\ProductInterface;

class ProductDuplicateFactory implements ProductInterface
{
    /**
     * The product.
     *
     * @var Product
     */
    protected $product;

    public function init(Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Duplicate a product
     *
     * @param Collection $data
     * @return Product
     */
    public function duplicate(Collection $data)
    {
        $product = DB::transaction(function () use ($data) {
            $newProduct = $this->product->replicate();

            $currentVariants = $newProduct->variants;
            $currentRoutes = $newProduct->routes;
            $newProduct->save();

            // Zero them out so we can add them back in.
            $newProduct->variants()->delete();
            $newProduct->routes()->delete();

            $newProduct->refresh();

            $this->processVariants($newProduct, $currentVariants, $data);
            $this->processRoutes($newProduct, $currentRoutes, $data);
            $this->processAssets($newProduct);
            $this->processCategories($newProduct);
            $this->processChannels($newProduct);
            $this->processCustomerGroups($newProduct);

            return $newProduct->load([
                'variants',
                'channels',
                'routes',
                'customerGroups',
            ]);
        });

        event(new IndexableSavedEvent($product));

        return $product;
    }

    /**
     * Process the assets for a duplicated product
     *
     * @param Product $newProduct
     * @return void
     */
    protected function processAssets($newProduct)
    {
        $currentAssets = $this->product->assets;
        $assets = collect();


        $currentAssets->each(function ($a) use ($assets, $newProduct) {
            $newAsset = $a->replicate();

            // Move the file to it's new location
            $newAsset->assetable_id = $newProduct->id;

            $newFilename = uniqid() . '_' . $newAsset->filename;

            try {
                Storage::disk($newAsset->source->disk)->copy(
                    "{$newAsset->location}/{$newAsset->filename}",
                    "{$newAsset->location}/{$newFilename}"
                );
                $newAsset->filename = $newFilename;
            } catch (FileNotFoundException $e) {
                $newAsset->save();
                return;
            }

            $newAsset->save();


            foreach ($a->transforms as $transform) {
                $newTransform = $transform->replicate();
                $newTransform->asset_id = $newAsset->id;
                $newFilename = uniqid() . '_' . $newTransform->filename;

                try {
                    Storage::disk($newAsset->source->disk)->copy(
                        "{$newTransform->location}/{$newTransform->filename}",
                        "{$newTransform->location}/{$newFilename}"
                    );
                } catch (FileNotFoundException $e) {
                    continue;
                }

                $newTransform->filename = $newFilename;
                $newTransform->save();
            }
        });
    }

    /**
     * Process the duplicated product categories
     *
     * @param Product $newProduct
     * @return void
     */
    protected function processCategories($newProduct)
    {
        $currentCategories = $this->product->categories;
        foreach ($currentCategories as $category) {
            $newProduct->categories()->attach($category);
        }
    }

    /**
     * Process the customer groups for the duplicated product
     *
     * @param Product $newProduct
     * @return void
     */
    protected function processCustomerGroups($newProduct)
    {
        // Need to associate all the channels the current product has
        // but make sure they are not active to start with.
        $groups = $this->product->customerGroups;

        $newGroups = collect();

        foreach ($groups as $group) {
            $newGroups->put($group->id, [
                'visible' => $group->pivot->visible,
                'purchasable' => $group->pivot->purchasable,
            ]);
        }
        $newProduct->customerGroups()->sync($newGroups->toArray());
    }

    /**
     * Process channels for a duplicated product
     *
     * @param Product $newProduct
     * @return void
     */
    protected function processChannels($newProduct)
    {
        // Need to associate all the channels the current product has
        // but make sure they are not active to start with.
        $channels = $this->product->channels;

        $newChannels = collect();

        foreach ($channels as $channel) {
            $newChannels->put($channel->id, [
                'published_at' => null,
            ]);
        }

        $newProduct->channels()->sync($newChannels->toArray());

    }

    /**
     * Process the variants for duplication
     *
     * @param Product $newProduct
     * @param Collection $currentVariants
     * @param Collection $data
     * @return void
     */
    protected function processVariants($newProduct, $currentVariants, $data)
    {
        foreach ($data['skus'] as $sku) {
            // Get the existing variant with this SKU.
            $variant = $this->getVariantToCopy($currentVariants, $sku['current']);
            if (!$variant) {
                continue;
            }
            $variant->product_id = $newProduct->id;
            $variant->sku = $sku['new'];
            $variant->save();
        }
    }

    /**
     * Process the routes for duplication
     *
     * @param Product $newProduct
     * @param Collection $currentRoutes
     * @param Collection $data
     * @return void
     */
    protected function processRoutes($newProduct, $currentRoutes, $data)
    {
        foreach ($data['routes'] as $route) {
            $routeToCopy = $currentRoutes->first(function ($r) use ($route) {
                return $r->slug == $route['current'];
            });

            if (!$route) {
                continue;
            }
            $newRoute = $routeToCopy->replicate();
            $newRoute->slug = $route['new'];
            $newRoute->element_id = $newProduct->id;
            $newRoute->save();
        }
    }

    /**
     * Get the variant to copy
     *
     * @param array $variants
     * @param string $sku
     * @return ProductVariant
     */
    protected function getVariantToCopy($variants, $sku)
    {
        $variant = $variants->first(function ($v) use ($sku) {
            return $v->sku == $sku;
        });
        if (!$variant) {
            return null;
        }
        return $variant->load([
            'tiers',
            'customerPricing',
        ])->replicate();
    }

}
