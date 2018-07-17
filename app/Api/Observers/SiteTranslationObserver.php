<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\CmsLog;
use App\Api\Models\SiteTranslation;

class SiteTranslationObserver
{
    /**
     * Listen to the SiteTranslations created event.
     *
     * @param  SiteTranslation  $siteTranslation
     * @return void
     */
    public function created(SiteTranslation $siteTranslation)
    {
//        CmsLog::log($siteTranslation, LogConstants::SITE_TRANSLATION_CREATED);
    }

    /**
     * Listen to the SiteTranslations updating event.
     *
     * @param  SiteTranslation  $siteTranslation
     * @return void
     */
    public function updating(SiteTranslation $siteTranslation)
    {
//        CmsLog::log($siteTranslation->getOriginal(), LogConstants::SITE_TRANSLATION_BEFORE_UPDATED);
    }

    /**
     * Listen to the SiteTranslations updated event.
     *
     * @param  SiteTranslation  $siteTranslation
     * @return void
     */
    public function updated(SiteTranslation $siteTranslation)
    {
//        CmsLog::log($siteTranslation, LogConstants::SITE_TRANSLATION_UPDATED);
    }

    /**
     * Listen to the SiteTranslations saved event.
     *
     * @param  SiteTranslation  $siteTranslation
     * @return void
     */
    public function saved(SiteTranslation $siteTranslation)
    {
//        CmsLog::log($siteTranslation, LogConstants::SITE_TRANSLATION_SAVED);
    }

    /**
     * Listen to the SiteTranslations deleting event.
     *
     * @param  SiteTranslation  $siteTranslation
     * @return void
     */
    public function deleting(SiteTranslation $siteTranslation)
    {
//        CmsLog::log($siteTranslation, LogConstants::SITE_TRANSLATION_BEFORE_DELETED);
    }
}