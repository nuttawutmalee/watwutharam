<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\Site;
use App\Api\Models\CmsLog;

class SiteObserver
{
    /**
     * Listen to the Sites created event.
     *
     * @param  Site  $site
     * @return void
     */
    public function created(Site $site)
    {
//        CmsLog::log($site, LogConstants::SITE_CREATED);
    }

    /**
     * Listen to the Sites updating event.
     *
     * @param  Site  $site
     * @return void
     */
    public function updating(Site $site)
    {
//        CmsLog::log($site->getOriginal(), LogConstants::SITE_BEFORE_UPDATED);
    }

    /**
     * Listen to the Sites updated event.
     *
     * @param  Site  $site
     * @return void
     */
    public function updated(Site $site)
    {
//        CmsLog::log($site, LogConstants::SITE_UPDATED);
    }

    /**
     * Listen to the Sites saved event.
     *
     * @param  Site  $site
     * @return void
     */
    public function saved(Site $site)
    {
//        CmsLog::log($site, LogConstants::SITE_SAVED);
    }

    /**
     * Listen to the Sites deleting event.
     *
     * @param  Site  $site
     * @return void
     */
    public function deleting(Site $site)
    {
//        CmsLog::log($site, LogConstants::SITE_BEFORE_DELETED);
    }
}