<?php

namespace App\Api\Observers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\GlobalItem;
use App\Api\Models\PageItem;
use App\Api\Models\CmsLog;

class PageItemObserver
{
    /**
     * Listen to the PageItems creating event.
     *
     * @param PageItem $pageItem
     * @throws \Exception
     */
    public function creating(PageItem $pageItem)
    {
        /** @var GlobalItem $globalItem */
        if ($globalItem = $pageItem->globalItem) {
            if ($globalItem->site->getKey() !== $pageItem->page->template->site->getKey()) throw new \Exception(ErrorMessageConstants::FROM_DIFFERENT_SITE);
        }

        if ($page = $pageItem->page) {
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            $duplicateQuery = PageItem::where('page_id', $page->getKey())->where('variable_name', $pageItem->variable_name);
            $count = $duplicateQuery->count();

            if ($count > 0) {
                throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
            }
        } else {
            throw new \Exception('Page not found');
        }
    }
    
    /**
     * Listen to the PageItems created event.
     *
     * @param  PageItem  $pageItem
     * @return void
     */
    public function created(PageItem $pageItem)
    {
        if ($component = $pageItem->component) {
            $pageItem->inheritComponentOptions();
        }

        CmsLog::log($pageItem, LogConstants::PAGE_ITEM_CREATED);
    }

    /**
     * Listen to the PageItems updating event.
     *
     * @param  PageItem  $pageItem
     * @throws \Exception
     * @return void
     */
    public function updating(PageItem $pageItem)
    {
        /** @var GlobalItem $globalItem */
        if ($globalItem = $pageItem->globalItem) {
            if ($globalItem->site->getKey() !== $pageItem->page->template->site->getKey()) throw new \Exception(ErrorMessageConstants::FROM_DIFFERENT_SITE);
        }

        if ($page = $pageItem->page) {
            if ( ! $pageItem->wasRecentlyCreated) {
                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                $duplicateQuery = PageItem::where('page_id', $page->getKey())
                    ->where($pageItem->getKeyName(), '!=', $pageItem->getKey())
                    ->where('variable_name', $pageItem->variable_name);
                $count = $duplicateQuery->count();

                if ($count > 0) {
                    throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
                }
            }

            CmsLog::log($pageItem->getOriginal(), LogConstants::PAGE_ITEM_BEFORE_UPDATED);
        } else {
            throw new \Exception('Page not found');
        }
    }

    /**
     * Listen to the PageItems updated event.
     *
     * @param  PageItem  $pageItem
     * @return void
     */
    public function updated(PageItem $pageItem)
    {
        CmsLog::log($pageItem, LogConstants::PAGE_ITEM_UPDATED);
    }

    /**
     * Listen to the PageItems saved event.
     *
     * @param  PageItem  $pageItem
     * @return void
     */
    public function saved(PageItem $pageItem)
    {
        CmsLog::log($pageItem, LogConstants::PAGE_ITEM_SAVED);
    }

    /**
     * Listen to the PageItems deleting event.
     *
     * @param  PageItem  $pageItem
     * @return void
     */
    public function deleting(PageItem $pageItem)
    {
        CmsLog::log($pageItem, LogConstants::PAGE_ITEM_BEFORE_DELETED);
    }
}