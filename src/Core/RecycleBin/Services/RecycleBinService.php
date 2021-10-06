<?php

namespace GetCandy\Api\Core\RecycleBin\Services;

use GetCandy\Api\Core\Channels\Actions\FetchCurrentChannel;
use GetCandy\Api\Core\Languages\Actions\FetchDefaultLanguage;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\RecycleBin\Interfaces\RecycleBinServiceInterface;
use GetCandy\Api\Core\RecycleBin\Models\RecycleBin;
use GetCandy\Api\Core\Search\Events\IndexableSavedEvent;

class RecycleBinService implements RecycleBinServiceInterface
{
    /**
     * Gets items that are currently soft deleted.
     *
     * @param  bool  $paginated
     * @param  int  $perPage
     * @param  mixed  $terms
     * @param  array  $includes
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getItems($paginated = true, $perPage = 25, $term = null, $includes = [])
    {
        $channel = FetchCurrentChannel::run();
        $language = FetchDefaultLanguage::run();
        $query = RecycleBin::whereHasMorph('recyclable', [
            Product::class,
        ], function ($query, $type) use ($term, $channel, $language) {
            if (! $term) {
                return;
            }
            if ($type == Product::class) {
                $query->leftJoin('product_variants', 'product_variants.product_id', '=', 'products.id')
                ->where(function ($queryTwo) use ($channel, $term, $language) {
                    $queryTwo->orWhere("attribute_data->name->{$channel->handle}->{$language->code}", 'LIKE', "%{$term}%")
                        ->orWhere('sku', 'LIKE', "%{$term}%");
                });
            }
        })->orderBy('created_at', 'desc');

        if (! $paginated) {
            return $query->get();
        }

        return $query->paginate($perPage);
    }

    public function findById($id, $includes = [])
    {
        return RecycleBin::with($includes)->findOrFail($id);
    }

    public function restore($id)
    {
        $item = $this->findById($id);
        if ($item->recyclable) {
            $item->recyclable->restore();
            IndexableSavedEvent::dispatch($item->recyclable);
            $item->delete();
        }
    }

    public function forceDelete($id)
    {
        $item = $this->findById($id);
        if (! $item->recyclable) {
            $item->delete();
        } else {
            $item->recyclable->forceDelete();
        }
    }
}
