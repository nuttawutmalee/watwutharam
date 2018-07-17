<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\TemplateItemOptionInteger;
use App\Api\Models\CmsLog;

class TemplateItemOptionIntegerObserver
{
    /**
     * Listen to the TemplateItemOptionIntegers created event.
     *
     * @param  TemplateItemOptionInteger  $templateItemOptionInteger
     * @return void
     */
    public function created(TemplateItemOptionInteger $templateItemOptionInteger)
    {
//        CmsLog::log($templateItemOptionInteger, LogConstants::TEMPLATE_ITEM_OPTION_INTEGER_CREATED);
    }

    /**
     * Listen to the TemplateItemOptionIntegers updating event.
     *
     * @param  TemplateItemOptionInteger  $templateItemOptionInteger
     * @throws \Exception
     * @return void
     */
    public function updating(TemplateItemOptionInteger $templateItemOptionInteger)
    {
//        CmsLog::log($templateItemOptionInteger->getOriginal(), LogConstants::TEMPLATE_ITEM_OPTION_INTEGER_BEFORE_UPDATED);
    }

    /**
     * Listen to the TemplateItemOptionIntegers updated event.
     *
     * @param  TemplateItemOptionInteger  $templateItemOptionInteger
     * @return void
     */
    public function updated(TemplateItemOptionInteger $templateItemOptionInteger)
    {
//        CmsLog::log($templateItemOptionInteger, LogConstants::TEMPLATE_ITEM_OPTION_INTEGER_UPDATED);
    }

    /**
     * Listen to the TemplateItemOptionIntegers saved event.
     *
     * @param  TemplateItemOptionInteger  $templateItemOptionInteger
     * @return void
     */
    public function saved(TemplateItemOptionInteger $templateItemOptionInteger)
    {
//        CmsLog::log($templateItemOptionInteger, LogConstants::TEMPLATE_ITEM_OPTION_INTEGER_SAVED);
    }

    /**
     * Listen to the TemplateItemOptionIntegers deleting event.
     *
     * @param  TemplateItemOptionInteger  $templateItemOptionInteger
     * @return void
     */
    public function deleting(TemplateItemOptionInteger $templateItemOptionInteger)
    {
//        CmsLog::log($templateItemOptionInteger, LogConstants::TEMPLATE_ITEM_OPTION_INTEGER_BEFORE_DELETED);
    }
}