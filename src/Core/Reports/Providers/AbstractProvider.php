<?php

namespace GetCandy\Api\Core\Reports\Providers;

use DateTime;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Orders\Models\OrderLine;

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
     * @var \DateTime
     */
    protected $from;

    /**
     * The from date for the query.
     *
     * @var \DateTime
     */
    protected $to;

    /**
     * Sets the date range for the provider.
     *
     * @param  \DateTime  $from
     * @param  \DateTime  $to
     * @return $this
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
     * @param  string  $mode
     * @return $this
     */
    public function mode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    protected function getDateFormat()
    {
        $format = '%Y-%m';
        $displayFormat = '%M %Y';

        if ($this->mode == 'weekly') {
            $format = '%Y-%v';
            $displayFormat = 'Week Comm. %d/%m/%Y';
        } elseif ($this->mode == 'daily') {
            $format = '%Y-%m-%d';
            $displayFormat = '%D %M %Y';
        }

        return [
            'format' => $format,
            'display' => $displayFormat,
        ];
    }

    /**
     * Gets order within the date range.
     *
     * @param  \DateTime  $from
     * @param  \DateTime  $to
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getOrderQuery(DateTime $from = null, DateTime $to = null)
    {
        return Order::whereNotNull('placed_at')
            ->whereBetween('placed_at', [
                $from ?: $this->from,
                $to ?: $this->to,
            ]);
    }

    /**
     * Gets order line within the date range.
     *
     * @param  \DateTime  $from
     * @param  \DateTime  $to
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getOrderLineQuery(DateTime $from = null, DateTime $to = null)
    {
        return OrderLine::whereHas('order', function ($query) use ($from, $to) {
            $query->withoutGlobalScope('not_expired')
            ->whereNotNull('placed_at')
            ->whereBetween('placed_at', [
                $from ?: $this->from,
                $to ?: $this->to,
            ]);
        });
    }
}
