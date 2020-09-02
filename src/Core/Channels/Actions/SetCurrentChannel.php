<?php

namespace GetCandy\Api\Core\Channels\Actions;

use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class SetCurrentChannel extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'handle' => 'string|nullable',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return void
     */
    public function handle(ChannelFactoryInterface $factory)
    {
        $factory->set($this->handle);
    }
}
