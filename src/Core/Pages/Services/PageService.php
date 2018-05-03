<?php

namespace GetCandy\Api\Core\Pages\Services;

use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Pages\Models\Page;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Exceptions\InvalidLanguageException;

class PageService extends BaseService
{
    public function __construct()
    {
        $this->model = new Page();
    }

    /**
     * Creates a page.
     * @param  array                $data
     * @param  string|Language      $languageCode
     * @param  string|Layout        $layout
     * @param  string|Channel       $channel
     * @param  string               $type
     * @param  Model|null           $relation
     * @throws InvalidLanguageException
     * @throws Symfony\Component\HttpKernel\Exception\HttpException
     * @return Page
     */
    public function create(array $data, $languageCode, $layout, $channel, $type = null, Model $relation = null)
    {
        $page = $this->model;

        /*
         * Figure out which language this page belongs to
         */
        if (! $languageCode instanceof Model) {
            $language = app('api')->languages()->getEnabledByCode($languageCode);
        } else {
            $language = $languageCode;
        }

        if (! $language) {
            throw new InvalidLanguageException(trans('response.error.invalid_lang', ['lang' => $languageCode]));
        }

        $page->language()->associate($language);

        /*
         * Sort out the layout for this page
         */
        if (! $layout instanceof Model) {
            $layout = app('api')->layouts()->getByHashedId($layout);
        }

        if (! $layout) {
            abort(400);
        }

        $page->layout()->associate($layout);

        /*
         * Sort out which channel this page belongs to
         */

        if (! $channel instanceof Model) {
            if ($channel) {
                $channel = app('api')->channels()->getByHashedId($channel);
            } else {
                $channel = app('api')->channels()->getDefaultRecord($channel);
            }
        }

        if (! $channel) {
            abort(400);
        }

        $page->channel()->associate($channel);

        // Fill'er up!
        $page->fill($data);
        $page->type = $type ?: 'page';
        if ($relation) {
            $relation->page()->save($page);
        } else {
            $page->save();
        }

        return $page;
    }

    /**
     * Finds a page based on it's channel, language and slug.
     * @param  string $channel
     * @param  string $lang
     * @param  string $slug
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     * @return mixed
     */
    public function findPage($channel, $lang, $slug = null)
    {
        if ($slug) {
            $result = $this->model->where(function ($q) use ($channel, $lang, $slug) {
                $q->whereHas('channel', function ($q2) use ($channel) {
                    $q2->where('handle', '=', $channel);
                });
                $q->whereHas('language', function ($q3) use ($lang) {
                    $q3->where('code', '=', $lang);
                });
                $q->where('slug', '=', $slug);
            });
        } else {
            $slug = $lang;
            $result = $this->model->where(function ($q) use ($channel, $lang, $slug) {
                $q->whereHas('channel', function ($q2) use ($channel) {
                    $q2->where('handle', '=', $channel);
                });
                $q->where('slug', '=', $slug);
            });
        }

        $model = $result->firstOrFail();

        if ($model->language->code != app()->getLocale()) {
            app()->setLocale($model->language->code);
        }

        return $result->firstOrFail();
    }

    /**
     * Gets a unique slug for a page.
     * @param  string $slug
     * @return string
     */
    protected function getUniqueSlug($slug)
    {
        $suffixe = '1';
        while ($this->model->where('slug', '=', $slug)->exists()) {
            $slug = $slug.'-'.$suffixe;
            $suffixe++;
        }

        return $slug;
    }
}
