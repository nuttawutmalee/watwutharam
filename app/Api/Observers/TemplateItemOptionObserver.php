<?php

namespace App\Api\Observers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\TemplateItemOption;
use App\Api\Models\CmsLog;

class TemplateItemOptionObserver
{
    /**
     * Listen to the TemplateItemOptions creating event.
     *
     * @param TemplateItemOption $templateItemOption
     * @throws \Exception
     */
    public function creating(TemplateItemOption $templateItemOption)
    {
        if ($item = $templateItemOption->templateItem) {
            $duplicateQuery = TemplateItemOption::where('template_item_id', $item->getKey())->where('variable_name', $templateItemOption->variable_name);
            $count = $duplicateQuery->count();

            if ($count > 0) {
                throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
            }
        } else {
            throw new \Exception('Template item not found');
        }
    }

    /**
     * Listen to the TemplateItemOptions created event.
     *
     * @param  TemplateItemOption  $templateItemOption
     * @return void
     */
    public function created(TemplateItemOption $templateItemOption)
    {
        CmsLog::log($templateItemOption, LogConstants::TEMPLATE_ITEM_OPTION_CREATED);
    }

    /**
     * Listen to the TemplateItemOptions updating event.
     *
     * @param  TemplateItemOption  $templateItemOption
     * @throws \Exception
     * @return void
     */
    public function updating(TemplateItemOption $templateItemOption)
    {
        if ($item = $templateItemOption->templateItem) {
            if ( ! $templateItemOption->wasRecentlyCreated) {
                $duplicateQuery = TemplateItemOption::where('template_item_id', $item->getKey())
                    ->where($templateItemOption->getKeyName(), '!=', $templateItemOption->getKey())
                    ->where('variable_name', $templateItemOption->variable_name);
                $count = $duplicateQuery->count();

                if ($count > 0) {
                    throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
                }
            }

            CmsLog::log($templateItemOption->getOriginal(), LogConstants::TEMPLATE_ITEM_OPTION_BEFORE_UPDATED);
        } else {
            throw new \Exception('Template item not found');
        }
    }

    /**
     * Listen to the TemplateItemOptions updated event.
     *
     * @param  TemplateItemOption  $templateItemOption
     * @return void
     */
    public function updated(TemplateItemOption $templateItemOption)
    {
        CmsLog::log($templateItemOption, LogConstants::TEMPLATE_ITEM_OPTION_UPDATED);
    }

    /**
     * Listen to the TemplateItemOptions saved event.
     *
     * @param  TemplateItemOption  $templateItemOption
     * @return void
     */
    public function saved(TemplateItemOption $templateItemOption)
    {
        CmsLog::log($templateItemOption, LogConstants::TEMPLATE_ITEM_OPTION_SAVED);
    }

    /**
     * Listen to the TemplateItemOptions deleting event.
     *
     * @param  TemplateItemOption  $templateItemOption
     * @return void
     */
    public function deleting(TemplateItemOption $templateItemOption)
    {
        CmsLog::log($templateItemOption, LogConstants::TEMPLATE_ITEM_OPTION_BEFORE_DELETED);
    }
}