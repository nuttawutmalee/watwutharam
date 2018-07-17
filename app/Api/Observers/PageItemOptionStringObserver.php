<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\PageItemOptionString;
use App\Api\Models\CmsLog;

class PageItemOptionStringObserver
{
    /**
     * Listen to the PageItemOptionStrings created event.
     *
     * @param  PageItemOptionString  $pageItemOptionString
     * @return void
     */
    public function created(PageItemOptionString $pageItemOptionString)
    {
//        CmsLog::log($pageItemOptionString, LogConstants::PAGE_ITEM_OPTION_STRING_CREATED);
    }

    /**
     * Listen to the PageItemOptionStrings updating event.
     *
     * @param  PageItemOptionString  $pageItemOptionString
     * @throws \Exception
     * @return void
     */
    public function updating(PageItemOptionString $pageItemOptionString)
    {
//        CmsLog::log($pageItemOptionString->getOriginal(), LogConstants::PAGE_ITEM_OPTION_STRING_BEFORE_UPDATED);
    }

    /**
     * Listen to the PageItemOptionStrings updated event.
     *
     * @param  PageItemOptionString  $pageItemOptionString
     * @return void
     */
    public function updated(PageItemOptionString $pageItemOptionString)
    {
//        CmsLog::log($pageItemOptionString, LogConstants::PAGE_ITEM_OPTION_STRING_UPDATED);
    }

    /**
     * Listen to the PageItemOptionStrings saved event.
     *
     * @param  PageItemOptionString  $pageItemOptionString
     * @return void
     */
    public function saved(PageItemOptionString $pageItemOptionString)
    {
//        CmsLog::log($pageItemOptionString, LogConstants::PAGE_ITEM_OPTION_STRING_SAVED);
    }

    /**
     * Listen to the PageItemOptionStrings deleting event.
     *
     * @param  PageItemOptionString  $pageItemOptionString
     * @return void
     */
    public function deleting(PageItemOptionString $pageItemOptionString)
    {
//        CmsLog::log($pageItemOptionString, LogConstants::PAGE_ITEM_OPTION_STRING_BEFORE_DELETED);
    }
}