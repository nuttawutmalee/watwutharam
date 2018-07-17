<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\ComponentOptionDecimal;
use App\Api\Models\CmsLog;

class ComponentOptionDecimalObserver
{
    /**
     * Listen to the ComponentOptionDecimals created event.
     *
     * @param  ComponentOptionDecimal  $componentOptionDecimal
     * @return void
     */
    public function created(ComponentOptionDecimal $componentOptionDecimal)
    {
//        CmsLog::log($componentOptionDecimal, LogConstants::COMPONENT_OPTION_DECIMAL_CREATED);
    }

    /**
     * Listen to the ComponentOptionDecimals updating event.
     *
     * @param  ComponentOptionDecimal  $componentOptionDecimal
     * @throws \Exception
     * @return void
     */
    public function updating(ComponentOptionDecimal $componentOptionDecimal)
    {
//        CmsLog::log($componentOptionDecimal->getOriginal(), LogConstants::COMPONENT_OPTION_DATE_BEFORE_UPDATED);
    }

    /**
     * Listen to the ComponentOptionDecimals updated event.
     *
     * @param  ComponentOptionDecimal  $componentOptionDecimal
     * @return void
     */
    public function updated(ComponentOptionDecimal $componentOptionDecimal)
    {
//        CmsLog::log($componentOptionDecimal, LogConstants::COMPONENT_OPTION_DATE_UPDATED);
    }

    /**
     * Listen to the ComponentOptionDecimals saved event.
     *
     * @param  ComponentOptionDecimal  $componentOptionDecimal
     * @return void
     */
    public function saved(ComponentOptionDecimal $componentOptionDecimal)
    {
//        CmsLog::log($componentOptionDecimal, LogConstants::COMPONENT_OPTION_DECIMAL_SAVED);
    }

    /**
     * Listen to the ComponentOptionDecimals deleting event.
     *
     * @param  ComponentOptionDecimal  $componentOptionDecimal
     * @return void
     */
    public function deleting(ComponentOptionDecimal $componentOptionDecimal)
    {
//        CmsLog::log($componentOptionDecimal, LogConstants::COMPONENT_OPTION_DECIMAL_BEFORE_DELETED);
    }
}