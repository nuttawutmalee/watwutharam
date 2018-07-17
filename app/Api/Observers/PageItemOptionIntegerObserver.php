<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\PageItemOptionInteger;
use App\Api\Models\CmsLog;

class PageItemOptionIntegerObserver
{
    /**
     * Listen to the PageItemOptionIntegers created event.
     *
     * @param  PageItemOptionInteger  $pageItemOptionInteger
     * @return void
     */
    public function created(PageItemOptionInteger $pageItemOptionInteger)
    {
//        CmsLog::log($pageItemOptionInteger, LogConstants::PAGE_ITEM_OPTION_INTEGER_CREATED);
    }

    /**
     * Listen to the PageItemOptionIntegers updating event.
     *
     * @param  PageItemOptionInteger  $pageItemOptionInteger
     * @throws \Exception
     * @return void
     */
    public function updating(PageItemOptionInteger $pageItemOptionInteger)
    {
//        CmsLog::log($pageItemOptionInteger->getOriginal(), LogConstants::PAGE_ITEM_OPTION_INTEGER_BEFORE_UPDATED);
    }

    /**
     * Listen to the PageItemOptionIntegers updated event.
     *
     * @param  PageItemOptionInteger  $pageItemOptionInteger
     * @return void
     */
    public function updated(PageItemOptionInteger $pageItemOptionInteger)
    {
//        CmsLog::log($pageItemOptionInteger, LogConstants::PAGE_ITEM_OPTION_INTEGER_UPDATED);
    }

    /**
     * Listen to the PageItemOptionIntegers saved event.
     *
     * @param  PageItemOptionInteger  $pageItemOptionInteger
     * @return void
     */
    public function saved(PageItemOptionInteger $pageItemOptionInteger)
    {
//        CmsLog::log($pageItemOptionInteger, LogConstants::PAGE_ITEM_OPTION_INTEGER_SAVED);
    }

    /**
     * Listen to the PageItemOptionIntegers deleting event.
     *
     * @param  PageItemOptionInteger  $pageItemOptionInteger
     * @return void
     */
    public function deleting(PageItemOptionInteger $pageItemOptionInteger)
    {
//        CmsLog::log($pageItemOptionInteger, LogConstants::PAGE_ITEM_OPTION_INTEGER_BEFORE_DELETED);
    }
}