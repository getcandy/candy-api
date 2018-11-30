<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Observers\LockedObserver;

trait Lockable
{
    /**
     * Set whether model is locked.
     *
     * @var bool
     */
    protected $locked = false;

    /**
     * Boot up the trait.
     *
     * @return void
     */
    public static function bootLockable()
    {
        static::observe(new LockedObserver);
    }

    /**
     * Lock the model.
     *
     * @return void
     */
    public function lock()
    {
        $this->locked = true;

        return $this;
    }

    /**
     * Unlock the model.
     *
     * @return void
     */
    public function unlock()
    {
        $this->locked = false;

        return $this;
    }

    /**
     * If the model is locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->locked;
    }
}
