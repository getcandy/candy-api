<?php

namespace GetCandy\Api\Core\Languages\Observers;

use GetCandy\Api\Core\Languages\Models\Language;

class LanguageObserver
{
    /**
     * Handle the Language "updated" event.
     *
     * @param  \GetCandy\Api\Core\Languages\Models\Language  $language
     * @return void
     */
    public function updated(Language $language)
    {
        if ($language->default) {
            $this->makeOtherRecordsNonDefault($language);
        }
    }

    /**
     * Handle the Channel "created" event.
     *
     * @param  \GetCandy\Api\Core\Languages\Models\Language  $language
     * @return void
     */
    public function created(Language $language)
    {
        if ($language->default) {
            $this->makeOtherRecordsNonDefault($language);
        }
    }

    /**
     * Sets records apart from the one passed to not be default.
     *
     * @param  \GetCandy\Api\Core\Languages\Models\Language  $language
     *
     * @return  void
     */
    protected function makeOtherRecordsNonDefault(Language $language)
    {
        Language::whereDefault(true)->where('id', '!=', $language->id)->update([
            'default' => false,
        ]);
    }
}
