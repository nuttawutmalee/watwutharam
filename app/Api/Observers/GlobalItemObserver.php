<?php

namespace App\Api\Observers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\GlobalItem;
use App\Api\Models\CmsLog;

class GlobalItemObserver
{
    /**
     * Listen to the GlobalItems creating event.
     *
     * @param GlobalItem $globalItem
     * @throws \Exception
     */
    public function creating(GlobalItem $globalItem)
    {
        if ($site = $globalItem->site) {
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            $duplicateQuery = GlobalItem::where('site_id', $site->getKey())->where('variable_name', $globalItem->variable_name);
            $count = $duplicateQuery->count();

            if ($count > 0) {
                throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
            }
        } else {
            throw new \Exception(ErrorMessageConstants::SITE_NOT_FOUND);
        }
    }
    
    /**
     * Listen to the GlobalItems created event.
     *
     * @param  GlobalItem  $globalItem
     * @return void
     */
    public function created(GlobalItem $globalItem)
    {
        if ($component = $globalItem->component) {
            $globalItem->inheritComponentOptions();
        }

        CmsLog::log($globalItem, LogConstants::GLOBAL_ITEM_CREATED);
    }

    /**
     * Listen to the GlobalItems updating event.
     *
     * @param  GlobalItem  $globalItem
     * @throws \Exception
     * @return void
     */
    public function updating(GlobalItem $globalItem)
    {
        if ($site = $globalItem->site) {
            if ( ! $globalItem->wasRecentlyCreated) {
                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                $duplicateQuery = GlobalItem::where('site_id', $site->getKey())
                    ->where($globalItem->getKeyName(), '!=', $globalItem->getKey())
                    ->where('variable_name', $globalItem->variable_name);
                $count = $duplicateQuery->count();

                if ($count > 0) {
                    throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
                }
            }

            CmsLog::log($globalItem->getOriginal(), LogConstants::GLOBAL_ITEM_BEFORE_UPDATED);
        } else {
            throw new \Exception(ErrorMessageConstants::SITE_NOT_FOUND);
        }
    }

    /**
     * Listen to the GlobalItems updated event.
     *
     * @param  GlobalItem  $globalItem
     * @return void
     */
    public function updated(GlobalItem $globalItem)
    {
        CmsLog::log($globalItem, LogConstants::GLOBAL_ITEM_UPDATED);
    }

    /**
     * Listen to the GlobalItems saved event.
     *
     * @param  GlobalItem  $globalItem
     * @return void
     */
    public function saved(GlobalItem $globalItem)
    {
        CmsLog::log($globalItem, LogConstants::GLOBAL_ITEM_SAVED);
    }

    /**
     * Listen to the GlobalItems deleting event.
     *
     * @param  GlobalItem  $globalItem
     * @return void
     */
    public function deleting(GlobalItem $globalItem)
    {
        CmsLog::log($globalItem, LogConstants::GLOBAL_ITEM_BEFORE_DELETED);
    }
    
    /**
     * Listen to the GlobalItems deleted event. 
     *
     * @param  GlobalItem  $globalItem
     * @return void
     */
     public function deleted(GlobalItem $globalItem)
     {
         CmsLog::log($globalItem, LogConstants::GLOBAL_ITEM_DELETED);
     }
}