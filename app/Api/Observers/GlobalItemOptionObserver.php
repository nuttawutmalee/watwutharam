<?php

namespace App\Api\Observers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\CmsLog;

class GlobalItemOptionObserver
{
    /**
     * Listen to the GlobalItemOptions creating event.
     *
     * @param GlobalItemOption $globalItemOption
     * @throws \Exception
     */
    public function creating(GlobalItemOption $globalItemOption)
    {
        if ($item = $globalItemOption->globalItem) {
            $duplicateQuery = GlobalItemOption::where('global_item_id', $item->getKey())->where('variable_name', $globalItemOption->variable_name);
            $count = $duplicateQuery->count();

            if ($count > 0) {
                throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
            }
        } else {
            throw new \Exception('Global item not found');
        }
    }
    
    /**
     * Listen to the GlobalItemOptions created event.
     *
     * @param  GlobalItemOption  $globalItemOption
     * @return void
     */
    public function created(GlobalItemOption $globalItemOption)
    {
        CmsLog::log($globalItemOption, LogConstants::GLOBAL_ITEM_OPTION_CREATED);
    }

    /**
     * Listen to the GlobalItemOptions updating event.
     *
     * @param  GlobalItemOption  $globalItemOption
     * @throws \Exception
     * @return void
     */
    public function updating(GlobalItemOption $globalItemOption)
    {
        if ($item = $globalItemOption->globalItem) {
            if ( ! $globalItemOption->wasRecentlyCreated) {
                $duplicateQuery = GlobalItemOption::where('global_item_id', $item->getKey())
                    ->where($globalItemOption->getKeyName(), '!=', $globalItemOption->getKey())
                    ->where('variable_name', $globalItemOption->variable_name);
                $count = $duplicateQuery->count();

                if ($count > 0) {
                    throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
                }
            }

            CmsLog::log($globalItemOption->getOriginal(), LogConstants::GLOBAL_ITEM_OPTION_BEFORE_UPDATED);
        } else {
            throw new \Exception('Global item not found');
        }
    }

    /**
     * Listen to the GlobalItemOptions updated event.
     *
     * @param  GlobalItemOption  $globalItemOption
     * @return void
     */
    public function updated(GlobalItemOption $globalItemOption)
    {
        CmsLog::log($globalItemOption, LogConstants::GLOBAL_ITEM_OPTION_UPDATED);
    }

    /**
     * Listen to the GlobalItemOptions saved event.
     *
     * @param  GlobalItemOption  $globalItemOption
     * @return void
     */
    public function saved(GlobalItemOption $globalItemOption)
    {
        CmsLog::log($globalItemOption, LogConstants::GLOBAL_ITEM_OPTION_SAVED);
    }

    /**
     * Listen to the GlobalItemOptions deleting event.
     *
     * @param  GlobalItemOption  $globalItemOption
     * @return void
     */
    public function deleting(GlobalItemOption $globalItemOption)
    {
        CmsLog::log($globalItemOption, LogConstants::GLOBAL_ITEM_OPTION_BEFORE_DELETED);
    }
}