<?php

namespace App\Api\Policies;

use App\Api\Models\RedirectUrl;
use App\Api\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RedirectUrlPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param $ability
     * @return bool|null
     */
    public function before(User $user, /** @noinspection PhpUnusedParameterInspection */ $ability)
    {
        return $user->isDeveloper() ? true : null;
    }

    /**
     * Determine whether the user can create redirectUrls.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        if ($user->isAdministrator()) {
            return ! is_null($user->role()->allowStructure()->first());
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can update the redirectUrl.
     *
     * @param  User  $user
     * @param  RedirectUrl  $redirectUrl
     * @return mixed
     */
    public function update(User $user, /** @noinspection PhpUnusedParameterInspection */ RedirectUrl $redirectUrl)
    {
        if ($user->isAdministrator()) {
            return ! is_null($user->role()->allowContent()->first());
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can delete the redirectUrl.
     *
     * @param  User  $user
     * @param  RedirectUrl  $redirectUrl
     * @return mixed
     */
    public function delete(User $user, /** @noinspection PhpUnusedParameterInspection */ RedirectUrl $redirectUrl)
    {
        if ($user->isAdministrator()) {
            return ! is_null($user->role()->allowStructure()->first());
        } else {
            return false;
        }
    }
}
