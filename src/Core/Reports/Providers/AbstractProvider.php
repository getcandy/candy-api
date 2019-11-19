<?php

namespace GetCandy\Api\Core\Reports\Providers;

use DB;
use DateTime;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;

abstract class AbstractProvider
{
    protected $colours = [
        '#E7028C',
        '#0099e5',
        '#00E5C5',
        '#0033E5',
        '#E6463F',
        '#633FE6',
        '#3FC9E6',
        '#3FE64F',
    ];

    /**
     * The report mode.
     *
     * @var string
     */
    protected $mode;

    const EXPRESSIONS = [
        'EQUALS' => "=",
        'LIKE' => "LIKE"
    ];

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

    protected $channels;

    protected $locale;

    public function __construct(ChannelFactoryInterface $channels)
    {
        $this->channels = $channels;
        $this->locale = app()->getLocale();
        $this->setUp();
    }

    protected function setUp()
    {
        return;
    }

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

    protected function getExpression($expression)
    {
        return $this::EXPRESSIONS[$expression] ?? $this::EXPRESSIONS['EQUALS'];
    }

    protected function getJsonColumn($attribute)
    {
        $channel = $this->channels->getChannel()->handle;
        return 'attribute_data->>"$.' . $attribute . '.' . $channel . '.' . $this->locale . '"';
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
