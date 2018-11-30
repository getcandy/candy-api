<?php

namespace GetCandy\Api\Core\Tags\Services;

use GetCandy\Api\Core\Tags\Models\Tag;
use GetCandy\Api\Core\Scaffold\BaseService;

class TagService extends BaseService
{
    protected $model;

    public function __construct()
    {
        $this->model = new Tag();
    }

    /**
     * Creates a resource from the given data.
     *
     * @param  array  $data
     *
     * @return GetCandy\Api\Core\Models\Tag
     */
    public function create(array $data)
    {
        $tag = new Tag();
        $tag->name = $data['name'];
        $tag->save();

        return $tag;
    }

    /**
     * Updates a resource from the given data.
     *
     * @param  string $id
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return GetCandy\Api\Core\Models\Tag
     */
    public function update($hashedId, array $data)
    {
        $tag = $this->getByHashedId($hashedId);

        if (! $tag) {
            abort(404);
        }

        $tag->fill($data);
        $tag->save();

        return $tag;
    }

    /**
     * Deletes a resource by its given hashed ID.
     *
     * @param  string $id
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return bool
     */
    public function delete($id)
    {
        $tag = $this->getByHashedId($id);

        if (! $tag) {
            abort(404);
        }

        return $tag->delete();
    }

    public function getTaggables(array $hashedIds, $type = null)
    {
        $ids = [];
        foreach ($hashedIds as $hash) {
            $ids[] = $this->model->decodeId($hash);
        }
        $query = $this->model->with(['taggables', 'taggables.records']);
        if ($type) {
            $query = $this->model->with(['taggables' => function ($query) use ($type) {
                $query->where('taggable_type', '=', $type);
            }, 'taggables.records']);
        }

        return $query->find($ids);
    }

    /**
     * Either returns an existing tag or makes a new one.
     * @param  string $value
     * @return Tag
     */
    public function getOrCreateTag($value)
    {
        $formatted = $this->getFormattedTagName($value);
        $result = $this->model->where('name', '=', $formatted)->first();
        if ($result) {
            return $result->toArray();
        }
        $tag = $this->create([
            'name' => $value,
        ]);

        return $tag->toArray();
    }

    /**
     * Returns an array of tag ids, ready for syncing.
     * @param  array  $tags
     * @return array
     */
    public function getSyncableIds(array $tags)
    {
        $ids = [];
        foreach ($tags as $tag) {
            if (! $tag['id']) {
                $tag = $this->getOrCreateTag($tag['name']);
            }
            $ids[] = $this->model->decodeId($tag['id']) ?: $tag['id'];
        }

        return $ids;
    }

    /**
     * Gets the tag name, formatted, ready to go.
     * @param  string $value
     * @return string
     */
    public function getFormattedTagName($value)
    {
        $format = config('tags.format');
        // Force an array, makes life easier...
        if (! is_array($format)) {
            $format = [$format];
        }
        foreach ($format as $callable) {
            $value = call_user_func($callable, $value);
        }

        return $value;
    }
}
