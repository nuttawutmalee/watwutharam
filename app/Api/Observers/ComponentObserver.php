<?php

namespace App\Api\Observers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\Component;
use App\Api\Models\CmsLog;

class ComponentObserver
{
    /**
     * Listen to the Components creating event.
     *
     * @param Component $component
     * @throws \Exception
     */
    public function creating(Component $component)
    {
        $duplicateQuery = Component::where('variable_name', $component->variable_name);
        $count = $duplicateQuery->count();

        if ($count > 0) {
            throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
        }
    }
    
    /**
     * Listen to the Components created event.
     *
     * @param  Component  $component
     * @return void
     */
    public function created(Component $component)
    {
        CmsLog::log($component, LogConstants::COMPONENT_CREATED);
    }

    /**
     * Listen to the Components updating event.
     *
     * @param  Component  $component
     * @throws \Exception
     * @return void
     */
    public function updating(Component $component)
    {
        if ( ! $component->wasRecentlyCreated) {
            $duplicateQuery = Component::where($component->getKeyName(), '!=', $component->getKey())->where('variable_name', $component->variable_name);
            $count = $duplicateQuery->count();

            if ($count > 0) {
                throw new \Exception(ErrorMessageConstants::DUPLICATE_VARIABLE_NAME, 500);
            }
        }

        CmsLog::log($component->getOriginal(), LogConstants::COMPONENT_BEFORE_UPDATED);
    }

    /**
     * Listen to the Components updated event.
     *
     * @param  Component  $component
     * @return void
     */
    public function updated(Component $component)
    {
        CmsLog::log($component, LogConstants::COMPONENT_UPDATED);
    }

    /**
     * Listen to the Components saved event.
     *
     * @param  Component  $component
     * @return void
     */
    public function saved(Component $component)
    {
        CmsLog::log($component, LogConstants::COMPONENT_SAVED);
    }

    /**
     * Listen to the Components deleting event.
     *
     * @param  Component  $component
     * @return void
     */
    public function deleting(Component $component)
    {
        CmsLog::log($component, LogConstants::COMPONENT_BEFORE_DELETED);
    }
}