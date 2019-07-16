<?php

namespace GetCandy\Api\Http\Transformers\Fractal;

use League\Fractal\TransformerAbstract;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use GetCandy\Api\Http\Transformers\Fractal\Assets\AssetTransformer;

abstract class BaseTransformer extends TransformerAbstract
{
    /**
     * Returns the correct translation for a name array.
     * @param  mixed $name
     * @return string
     */
    protected function getLocalisedName($name)
    {
        if (! is_array($name)) {
            $name = json_decode($name, true);
        }
        $locale = app()->getLocale();
        $requestLang = strtolower(app('request')->language);
        if ($requestLang) {
            if ($requestLang != 'all') {
                $locale = $requestLang;
            } else {
                return $name;
            }
        }
        if (! empty($name[$locale])) {
            $name = $name[$locale];
        } else {
            $name = array_shift($name);
        }

        return $name;
    }

    protected function getThumbnail($model)
    {
        $asset = $model->primaryAsset->first();
        if (! $asset) {
            return;
        }
        $data = $this->item($asset, new AssetTransformer);

        return app()->fractal->createData($data)->toArray();
    }

    protected function includeThumbnail($model)
    {
        $asset = $model->primaryAsset;
        if (! $asset) {
            return;
        }

        return $this->item($asset, new AssetTransformer);
    }

    protected function paginateInclude($relation, $parent, $params, $transformer)
    {
        $perPage = $params['per_page'][0] ?? 15;
        $currentPage = $params['page'][0] ?? 1;

        $paginatedData = $parent->{$relation}()->paginate($perPage, ['*'], 'page', $currentPage);

        return $this->collection($paginatedData->getCollection(), $transformer)
            ->setPaginator(new IlluminatePaginatorAdapter($paginatedData));
    }
}
