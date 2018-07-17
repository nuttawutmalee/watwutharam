<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\GlobalItemOptionString;
use App\Api\Models\CmsLog;

class GlobalItemOptionStringObserver
{
    /**
     * Listen to the GlobalItemOptionStrings created event.
     *
     * @param  GlobalItemOptionString  $globalItemOptionString
     * @return void
     */
    public function created(GlobalItemOptionString $globalItemOptionString)
    {
//        CmsLog::log($globalItemOptionString, LogConstants::GLOBAL_ITEM_OPTION_STRING_CREATED);
    }

    /**
     * Listen to the GlobalItemOptionStrings updating event.
     *
     * @param  GlobalItemOptionString  $globalItemOptionString
     * @throws \Exception
     * @return void
     */
    public function updating(GlobalItemOptionString $globalItemOptionString)
    {
//        CmsLog::log($globalItemOptionString->getOriginal(), LogConstants::GLOBAL_ITEM_OPTION_STRING_BEFORE_UPDATED);
    }

    /**
     * Listen to the GlobalItemOptionStrings updated event.
     *
     * @param  GlobalItemOptionString  $globalItemOptionString
     * @return void
     */
    public function updated(GlobalItemOptionString $globalItemOptionString)
    {
//        CmsLog::log($globalItemOptionString, LogConstants::GLOBAL_ITEM_OPTION_STRING_UPDATED);
    }

    /**
     * Listen to the GlobalItemOptionStrings saved event.
     *
     * @param  GlobalItemOptionString  $globalItemOptionString
     * @return void
     */
    public function saved(GlobalItemOptionString $globalItemOptionString)
    {
//        CmsLog::log($globalItemOptionString, LogConstants::GLOBAL_ITEM_OPTION_STRING_SAVED);
    }

    /**
     * Listen to the GlobalItemOptionStrings deleting event.
     *
     * @param  GlobalItemOptionString  $globalItemOptionString
     * @return void
     */
    public function deleting(GlobalItemOptionString $globalItemOptionString)
    {
//        CmsLog::log($globalItemOptionString, LogConstants::GLOBAL_ITEM_OPTION_STRING_BEFORE_DELETED);
    }
}