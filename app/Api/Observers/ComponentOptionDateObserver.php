<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\ComponentOptionDate;
use App\Api\Models\CmsLog;

class ComponentOptionDateObserver
{
    /**
     * Listen to the ComponentOptionDates created event.
     *
     * @param  ComponentOptionDate  $componentOptionDate
     * @return void
     */
    public function created(ComponentOptionDate $componentOptionDate)
    {
        //CmsLog::log($componentOptionDate, LogConstants::COMPONENT_OPTION_DATE_CREATED);
    }

    /**
     * Listen to the ComponentOptionDates updating event.
     *
     * @param  ComponentOptionDate  $componentOptionDate
     * @throws \Exception
     * @return void
     */
    public function updating(ComponentOptionDate $componentOptionDate)
    {
        //CmsLog::log($componentOptionDate->getOriginal(), LogConstants::COMPONENT_OPTION_DATE_BEFORE_UPDATED);
    }

    /**
     * Listen to the ComponentOptionDates updated event.
     *
     * @param  ComponentOptionDate  $componentOptionDate
     * @return void
     */
    public function updated(ComponentOptionDate $componentOptionDate)
    {
        //CmsLog::log($componentOptionDate, LogConstants::COMPONENT_OPTION_DATE_UPDATED);
    }

    /**
     * Listen to the ComponentOptionDates saved event.
     *
     * @param  ComponentOptionDate  $componentOptionDate
     * @return void
     */
    public function saved(ComponentOptionDate $componentOptionDate)
    {
        //CmsLog::log($componentOptionDate, LogConstants::COMPONENT_OPTION_DATE_SAVED);
    }

    /**
     * Listen to the ComponentOptionDates deleting event.
     *
     * @param  ComponentOptionDate  $componentOptionDate
     * @return void
     */
    public function deleting(ComponentOptionDate $componentOptionDate)
    {
        //CmsLog::log($componentOptionDate, LogConstants::COMPONENT_OPTION_DATE_BEFORE_DELETED);
    }
}