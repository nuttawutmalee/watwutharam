<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\CmsLog;
use App\Api\Models\Language;

class LanguageObserver
{
    /**
     * Listen to the Languages created event.
     *
     * @param  Language  $language
     * @return void
     */
    public function created(Language $language)
    {
//        CmsLog::log($language, LogConstants::LANGUAGE_CREATED);
    }

    /**
     * Listen to the Languages updating event.
     *
     * @param  Language  $language
     * @return void
     */
    public function updating(Language $language)
    {
//        CmsLog::log($language->getOriginal(), LogConstants::LANGUAGE_BEFORE_UPDATED);
    }

    /**
     * Listen to the Languages updated event.
     *
     * @param  Language  $language
     * @return void
     */
    public function updated(Language $language)
    {
//        CmsLog::log($language, LogConstants::LANGUAGE_UPDATED);
    }

    /**
     * Listen to the Languages saved event.
     *
     * @param  Language  $language
     * @return void
     */
    public function saved(Language $language)
    {
//        CmsLog::log($language, LogConstants::LANGUAGE_SAVED);
    }

    /**
     * Listen to the Languages deleting event.
     *
     * @param  Language  $language
     * @return void
     */
    public function deleting(Language $language)
    {
//        CmsLog::log($language, LogConstants::LANGUAGE_BEFORE_DELETED);
    }
}