<?php

namespace GetCandy\Api\Core\Languages\Observers;

use GetCandy\Api\Core\Languages\Models\Language;


class LanguageObserver
{
    /**
     * Handle the Channel "updated" event.
     *
     * @param  \GetCandy\Api\Core\Languages\Models\Language  $language
     * @return void
     */
    public function updated(Language $language)
    {
        if ($language->default) {
            Language::whereDefault(true)->where('id', '!=', $language->id)->update([
                'default' => false,
            ]);
        }
    }
}
