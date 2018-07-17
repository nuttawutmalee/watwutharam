<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\ComponentOptionString;
use App\Api\Models\CmsLog;

class ComponentOptionStringObserver
{
    /**
     * Listen to the ComponentOptionStrings created event.
     *
     * @param  ComponentOptionString  $componentOptionString
     * @return void
     */
    public function created(ComponentOptionString $componentOptionString)
    {
//        CmsLog::log($componentOptionString, LogConstants::COMPONENT_OPTION_STRING_CREATED);
    }

    /**
     * Listen to the ComponentOptionStrings updating event.
     *
     * @param  ComponentOptionString  $componentOptionString
     * @throws \Exception
     * @return void
     */
    public function updating(ComponentOptionString $componentOptionString)
    {
//        CmsLog::log($componentOptionString->getOriginal(), LogConstants::COMPONENT_OPTION_STRING_BEFORE_UPDATED);
    }

    /**
     * Listen to the ComponentOptionStrings updated event.
     *
     * @param  ComponentOptionString  $componentOptionString
     * @return void
     */
    public function updated(ComponentOptionString $componentOptionString)
    {
//        CmsLog::log($componentOptionString, LogConstants::COMPONENT_OPTION_STRING_UPDATED);
    }

    /**
     * Listen to the ComponentOptionStrings saved event.
     *
     * @param  ComponentOptionString  $componentOptionString
     * @return void
     */
    public function saved(ComponentOptionString $componentOptionString)
    {
//        CmsLog::log($componentOptionString, LogConstants::COMPONENT_OPTION_STRING_SAVED);
    }

    /**
     * Listen to the ComponentOptionStrings deleting event.
     *
     * @param  ComponentOptionString  $componentOptionString
     * @return void
     */
    public function deleting(ComponentOptionString $componentOptionString)
    {
//        CmsLog::log($componentOptionString, LogConstants::COMPONENT_OPTION_STRING_BEFORE_DELETED);
    }
}