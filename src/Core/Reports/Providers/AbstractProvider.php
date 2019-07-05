<?php

namespace GetCandy\Api\Core\Reports\Providers;

use DateTime;
use GetCandy\Api\Core\Orders\Models\Order;

abstract class AbstractProvider
{
    /**
     * The report mode.
     *
     * @var string
     */
    protected $mode;

    /**
     * The from date for the query.
     *
     * @var DateTime
     */
    protected $from;

    /**
     * The from date for the query.
     *
     * @var DateTime
     */
    protected $to;

    /**
     * Sets the date range for the provider.
     *
     * @param DateTime $from
     * @param DateTime $to
     * @return self
     */
    public function between(DateTime $from, DateTime $to)
    {
        $this->from = $from;
        $this->to = $to;

        return $this;
    }

    /**
     * Get the report result.
     *
     * @return array
     */
    abstract public function get();

    /**
     * Set the mode value.
     *
     * @param string $mode
     * @return self
     */
    public function mode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Gets order within the date range.
     * @return \Illuminate\Support\Collection
     */
    protected function getOrderQuery(DateTime $from = null, DateTime $to = null)
    {
        return Order::withoutGlobalScope('open')
            ->withoutGlobalScope('not_expired')
            ->whereNotNull('placed_at')
            ->whereBetween('placed_at', [
                $from ?: $this->from,
                $to ?: $this->to,
            ]);
    }
}
