<?php

namespace App\Api\Observers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\PageItemOption;
use App\Api\Models\CmsLog;

class PageItemOptionObserver
{
    /**
     * Listen to the PageItemOptions creating event.
     *
     * @param PageItemOption $pageItemOption
     * @throws \Exception
     */
    public function creating(PageItemOption $pageItemOption)
    {
        if ($item = $pageItemOption->pageItem) {
            $duplicateQuery = PageItemOption::where('page_item_id', $item->getKey())->where('variable_name', $pageItemOption->variable_name);
            $count = $duplicateQuery->count();

            if ($count > 0) {
                throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
            }
        } else {
            throw new \Exception('Page item not found');
        }
    }
    
    /**
     * Listen to the PageItemOptions created event.
     *
     * @param  PageItemOption  $pageItemOption
     * @return void
     */
    public function created(PageItemOption $pageItemOption)
    {
        CmsLog::log($pageItemOption, LogConstants::PAGE_ITEM_OPTION_CREATED);
    }

    /**
     * Listen to the PageItemOptions updating event.
     *
     * @param  PageItemOption  $pageItemOption
     * @throws \Exception
     * @return void
     */
    public function updating(PageItemOption $pageItemOption)
    {
        if ($item = $pageItemOption->pageItem) {
            if ( ! $pageItemOption->wasRecentlyCreated) {
                $duplicateQuery = PageItemOption::where('page_item_id', $item->getKey())
                    ->where($pageItemOption->getKeyName(), '!=', $pageItemOption->getKey())
                    ->where('variable_name', $pageItemOption->variable_name);
                $count = $duplicateQuery->count();

                if ($count > 0) {
                    throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
                }
            }

            CmsLog::log($pageItemOption->getOriginal(), LogConstants::PAGE_ITEM_OPTION_BEFORE_UPDATED);
        } else {
            throw new \Exception('Page item not found');
        }
    }

    /**
     * Listen to the PageItemOptions updated event.
     *
     * @param  PageItemOption  $pageItemOption
     * @return void
     */
    public function updated(PageItemOption $pageItemOption)
    {
        CmsLog::log($pageItemOption, LogConstants::PAGE_ITEM_OPTION_UPDATED);
    }

    /**
     * Listen to the PageItemOptions saved event.
     *
     * @param  PageItemOption  $pageItemOption
     * @return void
     */
    public function saved(PageItemOption $pageItemOption)
    {
        CmsLog::log($pageItemOption, LogConstants::PAGE_ITEM_OPTION_SAVED);
    }

    /**
     * Listen to the PageItemOptions deleting event.
     *
     * @param  PageItemOption  $pageItemOption
     * @return void
     */
    public function deleting(PageItemOption $pageItemOption)
    {
        CmsLog::log($pageItemOption, LogConstants::PAGE_ITEM_OPTION_BEFORE_DELETED);
    }
}