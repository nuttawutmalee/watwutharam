<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\TemplateItemOptionDate;
use App\Api\Models\CmsLog;

class TemplateItemOptionDateObserver
{
    /**
     * Listen to the TemplateItemOptionDates created event.
     *
     * @param  TemplateItemOptionDate  $templateItemOptionDate
     * @return void
     */
    public function created(TemplateItemOptionDate $templateItemOptionDate)
    {
//        CmsLog::log($templateItemOptionDate, LogConstants::TEMPLATE_ITEM_OPTION_DATE_CREATED);
    }

    /**
     * Listen to the TemplateItemOptionDates updating event.
     *
     * @param  TemplateItemOptionDate  $templateItemOptionDate
     * @throws \Exception
     * @return void
     */
    public function updating(TemplateItemOptionDate $templateItemOptionDate)
    {
//        CmsLog::log($templateItemOptionDate->getOriginal(), LogConstants::TEMPLATE_ITEM_OPTION_DATE_BEFORE_UPDATED);
    }

    /**
     * Listen to the TemplateItemOptionDates updated event.
     *
     * @param  TemplateItemOptionDate  $templateItemOptionDate
     * @return void
     */
    public function updated(TemplateItemOptionDate $templateItemOptionDate)
    {
//        CmsLog::log($templateItemOptionDate, LogConstants::TEMPLATE_ITEM_OPTION_DATE_UPDATED);
    }

    /**
     * Listen to the TemplateItemOptionDates saved event.
     *
     * @param  TemplateItemOptionDate  $templateItemOptionDate
     * @return void
     */
    public function saved(TemplateItemOptionDate $templateItemOptionDate)
    {
//        CmsLog::log($templateItemOptionDate, LogConstants::TEMPLATE_ITEM_OPTION_DATE_SAVED);
    }

    /**
     * Listen to the TemplateItemOptionDates deleting event.
     *
     * @param  TemplateItemOptionDate  $templateItemOptionDate
     * @return void
     */
    public function deleting(TemplateItemOptionDate $templateItemOptionDate)
    {
//        CmsLog::log($templateItemOptionDate, LogConstants::TEMPLATE_ITEM_OPTION_DATE_BEFORE_DELETED);
    }
}