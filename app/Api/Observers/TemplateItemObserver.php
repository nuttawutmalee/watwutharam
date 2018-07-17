<?php

namespace App\Api\Observers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\TemplateItem;
use App\Api\Models\CmsLog;

class TemplateItemObserver
{
    /**
     * Listen to the TemplateItems creating event.
     *
     * @param TemplateItem $templateItem
     * @throws \Exception
     */
    public function creating(TemplateItem $templateItem)
    {
        if ($template = $templateItem->template) {
            /** @noinspection PhpDynamicAsStaticMethodCallInspection */
            $duplicateQuery = TemplateItem::where('template_id', $template->getKey())->where('variable_name', $templateItem->variable_name);
            $count = $duplicateQuery->count();

            if ($count > 0) {
                throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
            }
        } else {
            throw new \Exception('Template not found');
        }
    }

    /**
     * Listen to the TemplateItems created event.
     *
     * @param  TemplateItem  $templateItem
     * @return void
     */
    public function created(TemplateItem $templateItem)
    {
        if ($component = $templateItem->component) {
            $templateItem->inheritComponentOptions();
        }

        CmsLog::log($templateItem, LogConstants::TEMPLATE_ITEM_CREATED);
    }

    /**
     * Listen to the TemplateItems updating event.
     *
     * @param  TemplateItem  $templateItem
     * @throws \Exception
     * @return void
     */
    public function updating(TemplateItem $templateItem)
    {
        if ($template = $templateItem->template) {
            if ( ! $templateItem->wasRecentlyCreated) {
                /** @noinspection PhpDynamicAsStaticMethodCallInspection */
                $duplicateQuery = TemplateItem::where('template_id', $template->getKey())
                    ->where($templateItem->getKeyName(), '!=', $templateItem->getKey())
                    ->where('variable_name', $templateItem->variable_name);
                $count = $duplicateQuery->count();

                if ($count > 0) {
                    throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
                }
            }

            CmsLog::log($templateItem->getOriginal(), LogConstants::TEMPLATE_ITEM_BEFORE_UPDATED);
        } else {
            throw new \Exception('Template not found');
        }
    }

    /**
     * Listen to the TemplateItems updated event.
     *
     * @param  TemplateItem  $templateItem
     * @return void
     */
    public function updated(TemplateItem $templateItem)
    {
        CmsLog::log($templateItem, LogConstants::TEMPLATE_ITEM_UPDATED);
    }

    /**
     * Listen to the TemplateItems saved event.
     *
     * @param  TemplateItem  $templateItem
     * @return void
     */
    public function saved(TemplateItem $templateItem)
    {
        CmsLog::log($templateItem, LogConstants::TEMPLATE_ITEM_SAVED);
    }

    /**
     * Listen to the TemplateItems deleting event.
     *
     * @param  TemplateItem  $templateItem
     * @return void
     */
    public function deleting(TemplateItem $templateItem)
    {
        CmsLog::log($templateItem, LogConstants::TEMPLATE_ITEM_BEFORE_DELETED);
    }
}