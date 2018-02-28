<?php
namespace GetCandy\Api\Orders\Models;

use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Auth\Models\User;
use GetCandy\Api\Traits\HasCompletion;
use GetCandy\Api\Baskets\Models\Basket;
use Illuminate\Database\Eloquent\Builder;
use GetCandy\Api\Payments\Models\Transaction;

class Order extends BaseModel
{
    protected $hashids = 'order';

    protected $fillable = [
        'lines'
    ];

    protected $required = [
        'total',
        'currency',
        'billing_firstname',
        'billing_lastname',
        'billing_address',
        'billing_city',
        'billing_country',
        'billing_zip'
    ];

    public function getRequiredAttribute()
    {
        return collect($this->required);
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('open', function (Builder $builder) {
            $builder->whereNull('placed_at');
        });

        static::addGlobalScope('not_expired', function (Builder $builder) {
            $builder->where('status', '!=', 'expired');
        });
    }

    public function scopeSearch($qb, $keywords)
    {
        $query = $qb->where('billing_firstname', 'LIKE', '%'.$keywords.'%')
            ->orWhere('id', '=', str_replace('#ORD-', '', $keywords))
            ->orWhere('reference', '=', str_replace('#INV-', '', $keywords));

        return $query;
    }

    public function getChargedAmountAttribute()
    {
        $total = 0;
        $transactions = $this->transactions()->charged()->pluck('amount');
        foreach ($transactions as $amount) {
            $total += $amount;
        }
        return $total;
    }

    /**
     * Gets the shipping details
     *
     * @return array
     */
    public function getShippingDetailsAttribute()
    {
        return $this->getDetails('shipping');
    }

    /**
     * Gets back the billing details
     *
     * @return array
     */
    public function getBillingDetailsAttribute()
    {
        return $this->getDetails('billing');
    }

    /**
     * Gets the details, mainly for contact info
     *
     * @param string $type
     *
     * @return array
     */
    protected function getDetails($type)
    {
        return collect($this->attributes)->filter(function ($value, $key) use ($type) {
            return strpos($key, $type . '_') === 0;
        })->mapWithKeys(function ($item, $key) use ($type) {
            $newkey = str_replace($type . '_', '', $key);
            return [$newkey => $item];
        })->toArray();
    }

    public function getRefAttribute()
    {
        return '#ORD-' . str_pad($this->id, 4, 0, STR_PAD_LEFT);
    }

    public function getInvoiceReferenceAttribute()
    {
        if ($this->reference) {
            return '#INV-' . str_pad($this->reference, 4, 0, STR_PAD_LEFT);
        }
        return null;
    }

    public function getCustomerNameAttribute()
    {
        $name = null;

        if ($billing = $this->getDetails('billing')) {
            $name = $billing['firstname'] . ' ' . $billing['lastname'];
        }

        if ($this->user) {
            if ($this->user->company_name) {
                $name = $this->user->company_name;
            } elseif ($this->user->name) {
                $name = $this->user->name;
            }
        }

        if (!$name || $name == ' ') {
            return 'Guest Checkout';
        }
        return $name;
    }

    /**
     * Get the basket lines
     *
     * @return void
     */
    public function lines()
    {
        return $this->hasMany(OrderLine::class);
    }

    public function basket()
    {
        return $this->belongsTo(Basket::class);
    }

    /**
     * Get the basket user
     *
     * @return User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function discounts()
    {
        // dd($this->id);
        return $this->hasMany(OrderDiscount::class);
    }
}
