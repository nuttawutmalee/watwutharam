<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\GlobalItemOptionDecimal;
use App\Api\Models\CmsLog;

class GlobalItemOptionDecimalObserver
{
    /**
     * Listen to the GlobalItemOptionDecimals created event.
     *
     * @param  GlobalItemOptionDecimal  $globalItemOptionDecimal
     * @return void
     */
    public function created(GlobalItemOptionDecimal $globalItemOptionDecimal)
    {
//        CmsLog::log($globalItemOptionDecimal, LogConstants::GLOBAL_ITEM_OPTION_DECIMAL_CREATED);
    }

    /**
     * Listen to the GlobalItemOptionDecimals updating event.
     *
     * @param  GlobalItemOptionDecimal  $globalItemOptionDecimal
     * @throws \Exception
     * @return void
     */
    public function updating(GlobalItemOptionDecimal $globalItemOptionDecimal)
    {
//        CmsLog::log($globalItemOptionDecimal->getOriginal(), LogConstants::GLOBAL_ITEM_OPTION_DECIMAL_BEFORE_UPDATED);
    }

    /**
     * Listen to the GlobalItemOptionDecimals updated event.
     *
     * @param  GlobalItemOptionDecimal  $globalItemOptionDecimal
     * @return void
     */
    public function updated(GlobalItemOptionDecimal $globalItemOptionDecimal)
    {
//        CmsLog::log($globalItemOptionDecimal, LogConstants::GLOBAL_ITEM_OPTION_DECIMAL_UPDATED);
    }

    /**
     * Listen to the GlobalItemOptionDecimals saved event.
     *
     * @param  GlobalItemOptionDecimal  $globalItemOptionDecimal
     * @return void
     */
    public function saved(GlobalItemOptionDecimal $globalItemOptionDecimal)
    {
//        CmsLog::log($globalItemOptionDecimal, LogConstants::GLOBAL_ITEM_OPTION_DECIMAL_SAVED);
    }

    /**
     * Listen to the GlobalItemOptionDecimals deleting event.
     *
     * @param  GlobalItemOptionDecimal  $globalItemOptionDecimal
     * @return void
     */
    public function deleting(GlobalItemOptionDecimal $globalItemOptionDecimal)
    {
//        CmsLog::log($globalItemOptionDecimal, LogConstants::GLOBAL_ITEM_OPTION_DECIMAL_BEFORE_DELETED);
    }
}