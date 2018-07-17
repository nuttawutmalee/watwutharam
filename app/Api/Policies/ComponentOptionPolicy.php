<?php

namespace App\Api\Policies;

use App\Api\Models\ComponentOption;
use App\Api\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ComponentOptionPolicy
{
    use HandlesAuthorization;

    /**
     * @param User $user
     * @param $ability
     * @return bool|null
     */
    public function before(User $user, /** @noinspection PhpUnusedParameterInspection */ $ability)
    {
        return $user->isDeveloper();
    }

    /**
     * Determine whether the user can create componentOptions.
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
     * Determine whether the user can update the componentOption.
     *
     * @param  User $user
     * @param  ComponentOption  $componentOption
     * @return mixed
     */
    public function update(User $user, /** @noinspection PhpUnusedParameterInspection */ ComponentOption $componentOption)
    {
        return ! is_null($user->role()->allowContent()->first());
    }

    /**
     * Determine whether the user can delete the componentOption.
     *
     * @param  User  $user
     * @param  ComponentOption  $componentOption
     * @return mixed
     */
    public function delete(User $user, /** @noinspection PhpUnusedParameterInspection */ ComponentOption $componentOption)
    {
        if ($user->isAdministrator()) {
            return ! is_null($user->role()->allowStructure()->first());
        } else {
            return false;
        }
    }
}
