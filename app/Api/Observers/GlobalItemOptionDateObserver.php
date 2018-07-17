<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\GlobalItemOptionDate;
use App\Api\Models\CmsLog;

class GlobalItemOptionDateObserver
{
    /**
     * Listen to the GlobalItemOptionDates created event.
     *
     * @param  GlobalItemOptionDate  $globalItemOptionDate
     * @return void
     */
    public function created(GlobalItemOptionDate $globalItemOptionDate)
    {
//        CmsLog::log($globalItemOptionDate, LogConstants::GLOBAL_ITEM_OPTION_DATE_CREATED);
    }

    /**
     * Listen to the GlobalItemOptionDates updating event.
     *
     * @param  GlobalItemOptionDate  $globalItemOptionDate
     * @throws \Exception
     * @return void
     */
    public function updating(GlobalItemOptionDate $globalItemOptionDate)
    {
//        CmsLog::log($globalItemOptionDate, LogConstants::GLOBAL_ITEM_OPTION_DATE_BEFORE_UPDATED);
    }

    /**
     * Listen to the GlobalItemOptionDates updated event.
     *
     * @param  GlobalItemOptionDate  $globalItemOptionDate
     * @return void
     */
    public function updated(GlobalItemOptionDate $globalItemOptionDate)
    {
//        CmsLog::log($globalItemOptionDate, LogConstants::GLOBAL_ITEM_OPTION_DATE_UPDATED);
    }

    /**
     * Listen to the GlobalItemOptionDates saved event.
     *
     * @param  GlobalItemOptionDate  $globalItemOptionDate
     * @return void
     */
    public function saved(GlobalItemOptionDate $globalItemOptionDate)
    {
//        CmsLog::log($globalItemOptionDate, LogConstants::GLOBAL_ITEM_OPTION_DATE_SAVED);
    }

    /**
     * Listen to the GlobalItemOptionDates deleting event.
     *
     * @param  GlobalItemOptionDate  $globalItemOptionDate
     * @return void
     */
    public function deleting(GlobalItemOptionDate $globalItemOptionDate)
    {
//        CmsLog::log($globalItemOptionDate, LogConstants::GLOBAL_ITEM_OPTION_DATE_BEFORE_DELETED);
    }
}