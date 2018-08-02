<?php

namespace GetCandy\Api\Core\Discounts\Entities;

class Discount
{
    public $applied = false;

    public $stop = false;

    protected $model;

    protected $criteria;

    protected $reward;

    public function __construct()
    {
        $this->criteria = collect();
    }

    public function setReward(RewardSet $reward)
    {
        $this->reward = $reward;

        return $this;
    }

    public function addCriteria(CriteriaSet $criteria)
    {
        $this->criteria->push($criteria);

        return $this;
    }

    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    public function getReward()
    {
        return $this->reward;
    }

    public function getRewards()
    {
        return collect($this->reward->getRewards());
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function getModel()
    {
        return $this->model;
    }
}
