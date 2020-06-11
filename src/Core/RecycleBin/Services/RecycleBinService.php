<?php

namespace GetCandy\Api\Core\RecycleBin\Services;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\RecycleBin\Interfaces\RecycleBinServiceInterface;
use GetCandy\Api\Core\RecycleBin\Models\RecycleBin;

class RecycleBinService implements RecycleBinServiceInterface
{
    /**
     * Gets items that are currently soft deleted.
     *
     * @param  bool  $paginated
     * @param  int  $perPage
     * @param  mixed  $terms
     * @param  array  $includes
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getItems($paginated = true, $perPage = 25, $terms = null, $includes = [])
    {
        $query = RecycleBin::whereDoesntHaveMorph('recyclable', [
            Product::class,
        ]);

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
