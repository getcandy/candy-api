<?php

namespace GetCandy\Api\Core\Orders;

use GetCandy\Api\Core\Orders\Models\Order;

class OrderSearchCriteria
{
    /**
     * How many orders per page.
     *
     * @var int
     */
    protected $per_page = 15;

    /**
     * The current page.
     *
     * @var int
     */
    protected $page = 1;

    /**
     * The user to get the orders for.
     *
     * @var \Illuminate\Eloquent\Database\Model
     */
    protected $user;

    /**
     * The order status to filter by.
     *
     * @var string
     */
    protected $status;

    /**
     * Any keywords to search on.
     *
     * @var string
     */
    protected $keywords;

    /**
     * The earliest date to get orders from.
     *
     * @var string
     */
    protected $from;

    /**
     * The latest date to get orders from.
     *
     * @var string
     */
    protected $to;

    /**
     * The order delivery zone to filter by.
     *
     * @var string
     */
    protected $zone;

    /**
     * The order type to filter on.
     *
     * @var string
     */
    protected $type;

    /**
     * The scopes to use.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * The scopes to take off.
     *
     * @var array
     */
    protected $without_scopes = [];

    /**
     * What user to restrict the query to.
     *
     * @var string
     */
    protected $restrict = true;

    /**
     * Set a value on the criteria.
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value)
    {
        if (property_exists($this, $key)) {
            $this->{$key} = $value;
        }

        return $this;
    }

    /**
     * Fill the criteria.
     *
     * @param array $values
     * @return self
     */
    public function fill($values = [])
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }

        return $this;
    }

    /**
     * Get all the criteria params.
     *
     * @return void
     */
    public function toParams()
    {
        return get_object_vars($this);
    }

    /**
     * Get the result from our defined criteria.
     *
     * @return string
     */
    public function get()
    {
        $query = Order::status($this->status)
            ->type($this->type)
            ->zone($this->zone)
            ->range($this->from, $this->to)
            ->search($this->keywords);

        foreach ($this->without_scopes as $scope) {
            $query = $query->withoutGlobalScope($scope);
        }

        if ($this->status == 'awaiting-payment') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('placed_at', 'desc');
        }

        if ($this->restrict) {
            $query->whereHas('user', function ($q) {
                $q->whereId($this->user->id);
            });
        }

        return $query->paginate($this->per_page, ['*'], 'page', $this->page);
    }
}
