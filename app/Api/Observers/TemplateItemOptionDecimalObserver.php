<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\TemplateItemOptionDecimal;
use App\Api\Models\CmsLog;

class TemplateItemOptionDecimalObserver
{
    /**
     * Listen to the TemplateItemOptionDecimals created event.
     *
     * @param  TemplateItemOptionDecimal  $templateItemOptionDecimal
     * @return void
     */
    public function created(TemplateItemOptionDecimal $templateItemOptionDecimal)
    {
//        CmsLog::log($templateItemOptionDecimal, LogConstants::TEMPLATE_ITEM_OPTION_DECIMAL_CREATED);
    }

    /**
     * Listen to the TemplateItemOptionDecimals updating event.
     *
     * @param  TemplateItemOptionDecimal  $templateItemOptionDecimal
     * @throws \Exception
     * @return void
     */
    public function updating(TemplateItemOptionDecimal $templateItemOptionDecimal)
    {
//        CmsLog::log($templateItemOptionDecimal->getOriginal(), LogConstants::TEMPLATE_ITEM_OPTION_DECIMAL_BEFORE_UPDATED);
    }

    /**
     * Listen to the TemplateItemOptionDecimals updated event.
     *
     * @param  TemplateItemOptionDecimal  $templateItemOptionDecimal
     * @return void
     */
    public function updated(TemplateItemOptionDecimal $templateItemOptionDecimal)
    {
//        CmsLog::log($templateItemOptionDecimal, LogConstants::TEMPLATE_ITEM_OPTION_DECIMAL_UPDATED);
    }

    /**
     * Listen to the TemplateItemOptionDecimals saved event.
     *
     * @param  TemplateItemOptionDecimal  $templateItemOptionDecimal
     * @return void
     */
    public function saved(TemplateItemOptionDecimal $templateItemOptionDecimal)
    {
//        CmsLog::log($templateItemOptionDecimal, LogConstants::TEMPLATE_ITEM_OPTION_DECIMAL_SAVED);
    }

    /**
     * Listen to the TemplateItemOptionDecimals deleting event.
     *
     * @param  TemplateItemOptionDecimal  $templateItemOptionDecimal
     * @return void
     */
    public function deleting(TemplateItemOptionDecimal $templateItemOptionDecimal)
    {
//        CmsLog::log($templateItemOptionDecimal, LogConstants::TEMPLATE_ITEM_OPTION_DECIMAL_BEFORE_DELETED);
    }
}