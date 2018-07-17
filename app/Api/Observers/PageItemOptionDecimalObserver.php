<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\PageItemOptionDecimal;
use App\Api\Models\CmsLog;

class PageItemOptionDecimalObserver
{
    /**
     * Listen to the PageItemOptionDecimals created event.
     *
     * @param  PageItemOptionDecimal  $pageItemOptionDecimal
     * @return void
     */
    public function created(PageItemOptionDecimal $pageItemOptionDecimal)
    {
//        CmsLog::log($pageItemOptionDecimal, LogConstants::PAGE_ITEM_OPTION_DECIMAL_CREATED);
    }

    /**
     * Listen to the PageItemOptionDecimals updating event.
     *
     * @param  PageItemOptionDecimal  $pageItemOptionDecimal
     * @throws \Exception
     * @return void
     */
    public function updating(PageItemOptionDecimal $pageItemOptionDecimal)
    {
//        CmsLog::log($pageItemOptionDecimal->getOriginal(), LogConstants::PAGE_ITEM_OPTION_DECIMAL_BEFORE_UPDATED);
    }

    /**
     * Listen to the PageItemOptionDecimals updated event.
     *
     * @param  PageItemOptionDecimal  $pageItemOptionDecimal
     * @return void
     */
    public function updated(PageItemOptionDecimal $pageItemOptionDecimal)
    {
//        CmsLog::log($pageItemOptionDecimal, LogConstants::PAGE_ITEM_OPTION_DECIMAL_UPDATED);
    }

    /**
     * Listen to the PageItemOptionDecimals saved event.
     *
     * @param  PageItemOptionDecimal  $pageItemOptionDecimal
     * @return void
     */
    public function saved(PageItemOptionDecimal $pageItemOptionDecimal)
    {
//        CmsLog::log($pageItemOptionDecimal, LogConstants::PAGE_ITEM_OPTION_DECIMAL_SAVED);
    }

    /**
     * Listen to the PageItemOptionDecimals deleting event.
     *
     * @param  PageItemOptionDecimal  $pageItemOptionDecimal
     * @return void
     */
    public function deleting(PageItemOptionDecimal $pageItemOptionDecimal)
    {
//        CmsLog::log($pageItemOptionDecimal, LogConstants::PAGE_ITEM_OPTION_DECIMAL_BEFORE_DELETED);
    }
}