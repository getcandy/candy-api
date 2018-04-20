<?php

namespace GetCandy\Api\Core\Languages\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Exceptions\MinimumRecordRequiredException;

class LanguageService extends BaseService
{
    public function __construct()
    {
        $this->model = new Language();
    }

    /**
     * Creates a resource from the given data.
     *
     * @param  array  $data
     *
     * @return GetCandy\Api\Core\Models\Language
     */
    public function create($data)
    {
        $language = new Language();
        $language->name = $data['name'];
        $language->lang = $data['lang'];
        $language->iso = $data['iso'];
        if ((empty($data['default']) && ! $this->model->count()) || ! empty($data['default'])) {
            $this->setNewDefault($language);
        }

        $language->save();

        return $language;
    }

    public function getEnabledByLang($lang)
    {
        $query = $this->model->where('enabled', '=', true);
        if (is_array($lang)) {
            return $query->whereIn('lang', $lang)->first();
        }

        return $query->where('lang', '=', $lang)->first();
    }

    /**
     * Updates a resource from the given data.
     *
     * @param  string $id
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception
     * @throws GetCandy\Api\Core\Exceptions\MinimumRecordRequiredException
     *
     * @return GetCandy\Api\Core\Models\Language
     */
    public function update($hashedId, array $data)
    {
        $language = $this->getByHashedId($hashedId);

        if (! $language) {
            abort(404);
        }

        if (! empty($data['name'])) {
            $language->name = $data['name'];
        }

        if (! empty($data['lang'])) {
            $language->lang = $data['lang'];
        }
        if (! empty($data['iso'])) {
            $language->iso = $data['iso'];
        }

        if (! empty($data['default'])) {
            $this->setNewDefault($language);
        }

        if ((isset($data['enabled']) && ! $data['enabled']) && $language->default) {
            // If we only have one record and we are trying to disable it, throw an exception
            if ($this->model->enabled()->count() == 1) {
                throw new MinimumRecordRequiredException(
                    trans('response.error.minimum_record')
                );
            }
            $newDefault = $this->getNewSuggestedDefault();
            $this->setNewDefault($newDefault);
            $newDefault->save();
        }

        $language->save();

        return $language;
    }

    /**
     * Deletes a resource by its given hashed ID.
     *
     * @param  string $id
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws GetCandy\Api\Core\Exceptions\MinimumRecordRequiredException
     *
     * @return bool
     */
    public function delete($id)
    {
        $language = $this->getByHashedId($id);

        if (! $language) {
            abort(404);
        }

        if ($this->model->enabled()->count() == 1) {
            throw new MinimumRecordRequiredException(
                trans('response.error.minimum_record')
            );
        }

        if ($language->default && $newDefault = $this->getNewSuggestedDefault()) {
            $newDefault->default = true;
            $newDefault->save();
        }

        return $language->delete();
    }

    /**
     * Checks all locales in the array exist.
     * @param  array  $locales
     * @return bool
     */
    public function allLocalesExist(array $locales)
    {
        return $this->model->whereIn('lang', $locales)->count() == count($locales);
    }
}
