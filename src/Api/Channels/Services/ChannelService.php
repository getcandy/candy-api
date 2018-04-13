<?php

namespace GetCandy\Api\Channels\Services;

use GetCandy\Api\Scaffold\BaseService;
use GetCandy\Api\Channels\Models\Channel;
use GetCandy\Exceptions\MinimumRecordRequiredException;

class ChannelService extends BaseService
{
    /**
     * @var AttributeGroup
     */
    protected $model;

    public function __construct()
    {
        $this->model = new Channel();
    }

    /**
     * Creates a resource from the given data.
     *
     * @param  array  $data
     *
     * @return GetCandy\Api\Models\Channel
     */
    public function create(array $data)
    {
        $channel = new Channel();
        $channel->name = $data['name'];
        $channel->handle = str_slug($channel->name);

        // If this is the first channel, make it default
        if (empty($data['default']) && ! $this->count()) {
            $channel->default = true;
        }

        if (! empty($data['default'])) {
            $this->setNewDefault($channel);
        } else {
            $channel->default = false;
        }

        $channel->save();

        return $channel;
    }

    /**
     * Updates a resource from the given data.
     *
     * @param  string $id
     * @param  array  $data
     *
     * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws GetCandy\Api\Exceptions\MinimumRecordRequiredException
     *
     * @return GetCandy\Api\Models\Channel
     */
    public function update($hashedId, array $data)
    {
        $channel = $this->getByHashedId($hashedId);

        if (! $channel) {
            return;
        }

        $channel->fill($data);

        if (! empty($data['default'])) {
            $this->setNewDefault($channel);
        }

        $channel->save();

        return $channel;
    }

    /**
     * @param $id
     * @return mixed
     * @throws MinimumRecordRequiredException
     */
    public function delete($id)
    {
        $channel = $this->getByHashedId($id);

        if (! $channel) {
            abort(404);
        }

        if ($this->model->count() == 1) {
            throw new MinimumRecordRequiredException(
                trans('response.error.minimum_record')
            );
        }

        if ($channel->default && $newDefault = $this->model->first()) {
            $newDefault->default = true;
            $newDefault->save();
        }

        $channel->products()->sync([]);
        $channel->categories()->sync([]);
        $channel->collections()->sync([]);
        foreach ($channel->discount as $discount) {
            $discount->delete();
        }

        return $channel->delete();
    }

    public function getChannelsWithAvailability($model, $relation)
    {
        $channels = $this->model->with([camel_case($relation) => function ($q) use ($model, $relation) {
            $q->where($relation.'.id', $model->id);
        }])->get();
        foreach ($channels as $channel) {
            $model = $channel->{camel_case($relation)}->first();
            $channel->published_at = $model ? $model->pivot->published_at : null;
        }

        return $channels;
    }
}
