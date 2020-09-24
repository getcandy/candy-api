<?php

namespace GetCandy\Api\Core\Orders\Models;

use Carbon\Carbon;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Payments\Models\Transaction;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Scopes\OrderScope;
use GetCandy\Api\Core\Traits\HasMeta;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends BaseModel
{
    use LogsActivity, HasMeta;

    protected static $recordEvents = ['created'];

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'order';

    protected $guarded = [];

    protected $dates = [
        'placed_at',
    ];

    protected $required = [
        'currency',
        'billing_firstname',
        'billing_lastname',
        'billing_address',
        'billing_city',
        'billing_country',
        'billing_zip',
    ];

    public function getRequiredAttribute()
    {
        return collect($this->required);
    }

    public function getDisplayIdAttribute()
    {
        return '#ORD-'.str_pad($this->id, 4, 0, STR_PAD_LEFT);
    }

    public static function bootHasCustomerGroups()
    {
        static::addGlobalScope(new CustomerGroupScope);
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new OrderScope);
    }

    /**
     * Define the placed scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $qb
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePlaced($qb)
    {
        return $qb->whereNotNull('placed_at');
    }

    /**
     * Define the Zone scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $qb
     * @param  string  $zone
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeZone($qb, $zone)
    {
        if (! $zone) {
            return $qb;
        }

        return $qb->whereHas('lines', function ($q) use ($zone) {
            return $q->where('option', '=', $zone);
        });
    }

    /**
     * Define the date range scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $qb
     * @param  string  $from
     * @param  null|string  $to
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRange($qb, $from, $to = null)
    {
        if ($from) {
            $qb->whereDate('created_at', '>=', Carbon::parse($from));
        }
        if ($to) {
            $qb->whereDate('created_at', '<=', Carbon::parse($to));
        }

        return $qb;
    }

    /**
     * Define the type scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $qb
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeType($qb, $type)
    {
        if (! $type) {
            return $qb;
        }
        if ($type == 'Unknown') {
            $qb->whereNull('type');

            return $qb->whereNull('type');
        }

        return $qb->where('type', '=', $type);
    }

    /**
     * Define the status scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $qb
     * @param  string  $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($qb, $status)
    {
        if (! $status) {
            return $qb;
        }

        return $qb->where('status', '=', $status);
    }

    /**
     * Define the search scope.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $qb
     * @param  string  $keywords
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($qb, $keywords)
    {
        if (! $keywords) {
            return $qb;
        }

        // Do some stuff.
        // Explode by commas
        $segments = explode(',', $keywords);

        $matches = [];

        foreach ($segments as $segment) {
            $segments = explode(' ', $segment);
            array_push($matches, $segments);
        }

        $matches = array_flatten($matches);

        if (count($matches) > 1) {
            $qb = $qb->where('billing_firstname', 'LIKE', '%'.$matches[0].'%')
                ->where('billing_lastname', 'LIKE', '%'.$matches[1].'%');
        } else {
            $qb = $qb->whereIn('billing_firstname', $matches)
            ->orWhereIn('billing_lastname', $matches);
        }

        // Need to be able to search on order total
        foreach ($matches as $match) {
            if (is_numeric($match)) {
                $qb->orWhere('order_total', '=', $match * 100);
            }
        }

        $qb->orWhereIn('id', $matches)
            ->orWhereIn('contact_email', $matches)
            ->orWhereIn('reference', $matches);

        return $qb;
    }

    /**
     * Gets the shipping details.
     *
     * @return array
     */
    public function getShippingDetailsAttribute()
    {
        return $this->getDetails('shipping');
    }

    /**
     * Gets back the billing details.
     *
     * @return array
     */
    public function getBillingDetailsAttribute()
    {
        return $this->getDetails('billing');
    }

    public function getTotalAttribute()
    {
        return $this->sub_total + $this->delivery_total + $this->tax_total;
    }

    /**
     * Gets the details, mainly for contact info.
     *
     * @param  string  $type
     * @return array
     */
    public function getDetails($type)
    {
        return collect($this->attributes)->filter(function ($value, $key) use ($type) {
            return strpos($key, $type.'_') === 0;
        })->mapWithKeys(function ($item, $key) use ($type) {
            $newkey = str_replace($type.'_', '', $key);

            return [$newkey => $item];
        })->toArray();
    }

    public function getInvoiceReferenceAttribute()
    {
        if ($this->reference) {
            return '#INV-'.str_pad($this->reference, 4, 0, STR_PAD_LEFT);
        }
    }

    public function getCustomerNameAttribute()
    {
        $name = null;

        if ($billing = $this->getDetails('billing')) {
            return $billing['firstname'].' '.$billing['lastname'];
        }

        if ($this->user) {
            if ($this->user->company_name) {
                $name = $this->user->company_name;
            } elseif ($this->user->name) {
                $name = $this->user->name;
            }
        }

        if (! $name || $name == ' ') {
            return 'Guest Checkout';
        }

        return $name;
    }

    /**
     * Get the basket lines.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lines()
    {
        return $this->hasMany(OrderLine::class)->orderBy('is_shipping', 'asc');
    }

    /**
     * Gets all order lines that are from the basket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function basketLines()
    {
        return $this->hasMany(OrderLine::class)->whereIsShipping(false)->whereIsManual(false);
    }

    public function shipping()
    {
        return $this->hasOne(OrderLine::class)->whereIsShipping(true);
    }

    public function basket()
    {
        return $this->belongsTo(Basket::class);
    }

    /**
     * Get the basket user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        $class = config('auth.providers.users.model', User::class);

        return $this->belongsTo($class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->orderBy('created_at', 'desc');
    }

    public function discounts()
    {
        return $this->hasMany(OrderDiscount::class);
    }
}
