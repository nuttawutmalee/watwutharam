<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\GlobalItemOptionInteger;
use App\Api\Models\CmsLog;

class GlobalItemOptionIntegerObserver
{
    /**
     * Listen to the GlobalItemOptionIntegers created event.
     *
     * @param  GlobalItemOptionInteger  $globalItemOptionInteger
     * @return void
     */
    public function created(GlobalItemOptionInteger $globalItemOptionInteger)
    {
//        CmsLog::log($globalItemOptionInteger, LogConstants::GLOBAL_ITEM_OPTION_INTEGER_CREATED);
    }

    /**
     * Listen to the GlobalItemOptionIntegers updating event.
     *
     * @param  GlobalItemOptionInteger  $globalItemOptionInteger
     * @throws \Exception
     * @return void
     */
    public function updating(GlobalItemOptionInteger $globalItemOptionInteger)
    {
//        CmsLog::log($globalItemOptionInteger->getOriginal(), LogConstants::GLOBAL_ITEM_OPTION_INTEGER_BEFORE_UPDATED);
    }

    /**
     * Listen to the GlobalItemOptionIntegers updated event.
     *
     * @param  GlobalItemOptionInteger  $globalItemOptionInteger
     * @return void
     */
    public function updated(GlobalItemOptionInteger $globalItemOptionInteger)
    {
//        CmsLog::log($globalItemOptionInteger, LogConstants::GLOBAL_ITEM_OPTION_INTEGER_UPDATED);
    }

    /**
     * Listen to the GlobalItemOptionIntegers saved event.
     *
     * @param  GlobalItemOptionInteger  $globalItemOptionInteger
     * @return void
     */
    public function saved(GlobalItemOptionInteger $globalItemOptionInteger)
    {
//        CmsLog::log($globalItemOptionInteger, LogConstants::GLOBAL_ITEM_OPTION_INTEGER_SAVED);
    }

    /**
     * Listen to the GlobalItemOptionIntegers deleting event.
     *
     * @param  GlobalItemOptionInteger  $globalItemOptionInteger
     * @return void
     */
    public function deleting(GlobalItemOptionInteger $globalItemOptionInteger)
    {
//        CmsLog::log($globalItemOptionInteger, LogConstants::GLOBAL_ITEM_OPTION_INTEGER_BEFORE_DELETED);
    }
}