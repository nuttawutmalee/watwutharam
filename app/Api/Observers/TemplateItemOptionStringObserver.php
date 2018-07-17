<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\TemplateItemOptionString;
use App\Api\Models\CmsLog;

class TemplateItemOptionStringObserver
{
    /**
     * Listen to the TemplateItemOptionStrings created event.
     *
     * @param  TemplateItemOptionString  $templateItemOptionString
     * @return void
     */
    public function created(TemplateItemOptionString $templateItemOptionString)
    {
//        CmsLog::log($templateItemOptionString, LogConstants::TEMPLATE_ITEM_OPTION_STRING_CREATED);
    }

    /**
     * Listen to the TemplateItemOptionStrings updating event.
     *
     * @param  TemplateItemOptionString  $templateItemOptionString
     * @throws \Exception
     * @return void
     */
    public function updating(TemplateItemOptionString $templateItemOptionString)
    {
//        CmsLog::log($templateItemOptionString, LogConstants::TEMPLATE_ITEM_OPTION_STRING_BEFORE_UPDATED);
    }

    /**
     * Listen to the TemplateItemOptionStrings updated event.
     *
     * @param  TemplateItemOptionString  $templateItemOptionString
     * @return void
     */
    public function updated(TemplateItemOptionString $templateItemOptionString)
    {
//        CmsLog::log($templateItemOptionString, LogConstants::TEMPLATE_ITEM_OPTION_STRING_UPDATED);
    }

    /**
     * Listen to the TemplateItemOptionStrings saved event.
     *
     * @param  TemplateItemOptionString  $templateItemOptionString
     * @return void
     */
    public function saved(TemplateItemOptionString $templateItemOptionString)
    {
//        CmsLog::log($templateItemOptionString, LogConstants::TEMPLATE_ITEM_OPTION_STRING_SAVED);
    }

    /**
     * Listen to the TemplateItemOptionStrings deleting event.
     *
     * @param  TemplateItemOptionString  $templateItemOptionString
     * @return void
     */
    public function deleting(TemplateItemOptionString $templateItemOptionString)
    {
//        CmsLog::log($templateItemOptionString, LogConstants::TEMPLATE_ITEM_OPTION_STRING_BEFORE_DELETED);
    }
}