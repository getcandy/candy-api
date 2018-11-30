<?php

namespace GetCandy\Api\Observers;

use GetCandy\Api\Exceptions\ModelLockedException;

class LockedObserver
{
    public function saving($model)
    {
        if ($model->isLocked()) {
            throw new ModelLockedException(
                trans('getcandy::exceptions.model_locked')
            );

            return false;
        }

        return true;
    }
}
