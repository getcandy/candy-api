<?php

namespace GetCandy\Api\Core\Discounts;

class RewardSet
{
    protected $rewards;

    public function add($reward)
    {
        $this->rewards[] = $reward;

        return $this;
    }

    public function getRewards()
    {
        return $this->rewards;
    }
}
