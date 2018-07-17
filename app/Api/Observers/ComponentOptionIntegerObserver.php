<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\ComponentOptionInteger;
use App\Api\Models\CmsLog;

class ComponentOptionIntegerObserver
{
    /**
     * Listen to the ComponentOptionIntegers created event.
     *
     * @param  ComponentOptionInteger  $componentOptionInteger
     * @return void
     */
    public function created(ComponentOptionInteger $componentOptionInteger)
    {
//        CmsLog::log($componentOptionInteger, LogConstants::COMPONENT_OPTION_INTEGER_CREATED);
    }

    /**
     * Listen to the ComponentOptionIntegers updating event.
     *
     * @param  ComponentOptionInteger  $componentOptionInteger
     * @throws \Exception
     * @return void
     */
    public function updating(ComponentOptionInteger $componentOptionInteger)
    {
//        CmsLog::log($componentOptionInteger->getOriginal(), LogConstants::COMPONENT_OPTION_INTEGER_BEFORE_UPDATED);
    }

    /**
     * Listen to the ComponentOptionIntegers updated event.
     *
     * @param  ComponentOptionInteger  $componentOptionInteger
     * @return void
     */
    public function updated(ComponentOptionInteger $componentOptionInteger)
    {
//        CmsLog::log($componentOptionInteger, LogConstants::COMPONENT_OPTION_INTEGER_UPDATED);
    }

    /**
     * Listen to the ComponentOptionIntegers saved event.
     *
     * @param  ComponentOptionInteger  $componentOptionInteger
     * @return void
     */
    public function saved(ComponentOptionInteger $componentOptionInteger)
    {
//        CmsLog::log($componentOptionInteger, LogConstants::COMPONENT_OPTION_INTEGER_SAVED);
    }

    /**
     * Listen to the ComponentOptionIntegers deleting event.
     *
     * @param  ComponentOptionInteger  $componentOptionInteger
     * @return void
     */
    public function deleting(ComponentOptionInteger $componentOptionInteger)
    {
//        CmsLog::log($componentOptionInteger, LogConstants::COMPONENT_OPTION_INTEGER_BEFORE_DELETED);
    }
}