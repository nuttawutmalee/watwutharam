<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\CmsRole;
use App\Api\Models\CmsLog;

class CmsRoleObserver
{
    /**
     * Listen to the CmsRoles created event.
     *
     * @param  CmsRole  $cmsRole
     * @return void
     */
    public function created(CmsRole $cmsRole)
    {
        //CmsLog::log($cmsRole, LogConstants::CMS_ROLE_CREATED);
    }

    /**
     * Listen to the CmsRoles updating event.
     *
     * @param  CmsRole  $cmsRole
     * @return void
     */
    public function updating(CmsRole $cmsRole)
    {
        //CmsLog::log($cmsRole->getOriginal(), LogConstants::CMS_ROLE_BEFORE_UPDATED);
    }

	/**
     * Listen to the CmsRoles updated event.
     *
     * @param  CmsRole  $cmsRole
     * @return void
     */
    public function updated(CmsRole $cmsRole)
    {
        //CmsLog::log($cmsRole, LogConstants::CMS_ROLE_UPDATED);
    }

	/**
     * Listen to the CmsRoles saved event.
     *
     * @param  CmsRole  $cmsRole
     * @return void
     */
    public function saved(CmsRole $cmsRole)
    {
        //CmsLog::log($cmsRole, LogConstants::CMS_ROLE_SAVED);
    }

	/**
     * Listen to the CmsRoles deleting event.
     *
     * @param  CmsRole  $cmsRole
     * @return void
     */
    public function deleting(CmsRole $cmsRole)
    {
        //CmsLog::log($cmsRole, LogConstants::CMS_ROLE_BEFORE_DELETED);
    }
}