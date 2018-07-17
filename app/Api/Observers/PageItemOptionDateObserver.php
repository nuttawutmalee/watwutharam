<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\PageItemOptionDate;
use App\Api\Models\CmsLog;

class PageItemOptionDateObserver
{
    /**
     * Listen to the PageItemOptionDates created event.
     *
     * @param  PageItemOptionDate  $pageItemOptionDate
     * @return void
     */
    public function created(PageItemOptionDate $pageItemOptionDate)
    {
//        CmsLog::log($pageItemOptionDate, LogConstants::PAGE_ITEM_OPTION_DATE_CREATED);
    }

    /**
     * Listen to the PageItemOptionDates updating event.
     *
     * @param  PageItemOptionDate  $pageItemOptionDate
     * @throws \Exception
     * @return void
     */
    public function updating(PageItemOptionDate $pageItemOptionDate)
    {
//        CmsLog::log($pageItemOptionDate->getOriginal(), LogConstants::PAGE_ITEM_OPTION_DATE_BEFORE_UPDATED);
    }

    /**
     * Listen to the PageItemOptionDates updated event.
     *
     * @param  PageItemOptionDate  $pageItemOptionDate
     * @return void
     */
    public function updated(PageItemOptionDate $pageItemOptionDate)
    {
//        CmsLog::log($pageItemOptionDate, LogConstants::PAGE_ITEM_OPTION_DATE_UPDATED);
    }

    /**
     * Listen to the PageItemOptionDates saved event.
     *
     * @param  PageItemOptionDate  $pageItemOptionDate
     * @return void
     */
    public function saved(PageItemOptionDate $pageItemOptionDate)
    {
//        CmsLog::log($pageItemOptionDate, LogConstants::PAGE_ITEM_OPTION_DATE_SAVED);
    }

    /**
     * Listen to the PageItemOptionDates deleting event.
     *
     * @param  PageItemOptionDate  $pageItemOptionDate
     * @return void
     */
    public function deleting(PageItemOptionDate $pageItemOptionDate)
    {
//        CmsLog::log($pageItemOptionDate, LogConstants::PAGE_ITEM_OPTION_DATE_BEFORE_DELETED);
    }
}