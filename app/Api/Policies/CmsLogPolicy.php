<?php

namespace App\Api\Policies;

use App\Api\Models\CmsLog;
use App\Api\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CmsLogPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param $ability
     * @return bool|null
     */
    public function before(User $user, /** @noinspection PhpUnusedParameterInspection */ $ability)
    {
        if ($user->isDeveloper()) return true;

        if ($user->isAdministrator()){
            return ! is_null($user->role()->allowStructure()->first());
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can create cmsLogs.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(/** @noinspection PhpUnusedParameterInspection */ User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the cmsLog.
     *
     * @param  User $user
     * @param  CmsLog  $cmsLog
     * @return mixed
     */
    public function update(/** @noinspection PhpUnusedParameterInspection */ User $user, CmsLog $cmsLog)
    {
        return true;
    }

    /**
     * Determine whether the user can delete the cmsLog.
     *
     * @param  User  $user
     * @param  CmsLog  $cmsLog
     * @return mixed
     */
    public function delete(/** @noinspection PhpUnusedParameterInspection */ User $user, CmsLog $cmsLog)
    {
        return true;
    }
}
