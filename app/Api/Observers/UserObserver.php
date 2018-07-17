<?php

namespace App\Api\Observers;

use App\Api\Constants\LogConstants;
use App\Api\Models\User;
use App\Api\Models\CmsLog;

class UserObserver
{
    /**
     * Listen to the Users created event.
     *
     * @param  User  $user
     * @return void
     */
    public function created(User $user)
    {
        CmsLog::log($user, LogConstants::USER_CREATED);
    }

    /**
     * Listen to the Users updating event.
     *
     * @param  User  $user
     * @return void
     */
    public function updating(User $user)
    {
        CmsLog::log($user->getOriginal(), LogConstants::USER_BEFORE_UPDATED);
    }

    /**
     * Listen to the Users updated event.
     *
     * @param  User  $user
     * @return void
     */
    public function updated(User $user)
    {
        CmsLog::log($user, LogConstants::USER_UPDATED);
    }

    /**
     * Listen to the Users saved event.
     *
     * @param  User  $user
     * @return void
     */
    public function saved(User $user)
    {
        CmsLog::log($user, LogConstants::USER_SAVED);
    }

    /**
     * Listen to the Users deleting event.
     *
     * @param  User  $user
     * @return void
     */
    public function deleting(User $user)
    {
        CmsLog::log($user, LogConstants::USER_BEFORE_DELETED);
    }
}