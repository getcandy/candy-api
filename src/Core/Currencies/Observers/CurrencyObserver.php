<?php

namespace GetCandy\Api\Core\Currencies\Observers;

use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Currencies\Models\Currency;

class CurrencyObserver
{
    /**
     * Handle the Language "updated" event.
     *
     * @param  \GetCandy\Api\Core\Currencies\Models\Currency  $currency
     * @return void
     */
    public function updated(Currency $currency)
    {
        if ($currency->default) {
            $this->makeOtherRecordsNonDefault($currency);
        }
    }

    /**
     * Handle the Channel "created" event.
     *
     * @param  \GetCandy\Api\Core\Currencies\Models\Currency  $currency
     * @return void
     */
    public function created(Currency $currency)
    {
        if ($currency->default) {
            $this->makeOtherRecordsNonDefault($currency);
        }
    }

    /**
     * Sets records apart from the one passed to not be default.
     *
     * @param  \GetCandy\Api\Core\Currencies\Models\Currency  $currency
     *
     * @return  void
     */
    protected function makeOtherRecordsNonDefault(Currency $currency)
    {
        Currency::whereDefault(true)->where('id', '!=', $currency->id)->update([
            'default' => false,
        ]);
    }
}
