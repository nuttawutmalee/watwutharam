<?php

namespace App\Api\Observers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\ComponentOption;
use App\Api\Models\CmsLog;

class ComponentOptionObserver
{
    /**
     * Listen to the ComponentOptions creating event.
     *
     * @param ComponentOption $componentOption
     * @throws \Exception
     */
    public function creating(ComponentOption $componentOption)
    {
        if ($component = $componentOption->component) {
            $duplicateQuery = ComponentOption::where('component_id', $component->getKey())->where('variable_name', $componentOption->variable_name);
            $count = $duplicateQuery->count();

            if ($count > 0) {
                throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
            }
        } else {
            throw new \Exception('Component not found');
        }
    }
    
    /**
     * Listen to the ComponentOptions created event.
     *
     * @param  ComponentOption  $componentOption
     * @return void
     */
    public function created(ComponentOption $componentOption)
    {
        CmsLog::log($componentOption, LogConstants::COMPONENT_OPTION_CREATED);
    }

    /**
     * Listen to the ComponentOptions updating event.
     *
     * @param  ComponentOption  $componentOption
     * @throws \Exception
     * @return void
     */
    public function updating(ComponentOption $componentOption)
    {
        if ($component = $componentOption->component) {
            if ( ! $componentOption->wasRecentlyCreated) {
                $duplicateQuery = ComponentOption::where('component_id', $component->getKey())
                    ->where($componentOption->getKeyName(), '!=', $componentOption->getKey())
                    ->where('variable_name', $componentOption->variable_name);
                $count = $duplicateQuery->count();

                if ($count > 0) {
                    throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
                }
            }

            CmsLog::log($componentOption->getOriginal(), LogConstants::COMPONENT_OPTION_BEFORE_UPDATED);
        } else {
            throw new \Exception('Component not found');
        }
    }

    /**
     * Listen to the ComponentOptions updated event.
     *
     * @param  ComponentOption  $componentOption
     * @return void
     */
    public function updated(ComponentOption $componentOption)
    {
        CmsLog::log($componentOption, LogConstants::COMPONENT_OPTION_UPDATED);
    }

    /**
     * Listen to the ComponentOptions saved event.
     *
     * @param  ComponentOption  $componentOption
     * @return void
     */
    public function saved(ComponentOption $componentOption)
    {
        CmsLog::log($componentOption, LogConstants::COMPONENT_OPTION_SAVED);
    }

    /**
     * Listen to the ComponentOptions deleting event.
     *
     * @param  ComponentOption  $componentOption
     * @return void
     */
    public function deleting(ComponentOption $componentOption)
    {
        CmsLog::log($componentOption, LogConstants::COMPONENT_OPTION_BEFORE_DELETED);
    }
}