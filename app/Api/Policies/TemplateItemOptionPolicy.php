<?php

namespace App\Api\Policies;

use App\Api\Models\TemplateItemOption;
use App\Api\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TemplateItemOptionPolicy
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
     * Determine whether the user can create templateItemOptions.
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
     * Determine whether the user can update the templateItemOption.
     *
     * @param  User $user
     * @param  TemplateItemOption  $templateItemOption
     * @return mixed
     */
    public function update(User $user, /** @noinspection PhpUnusedParameterInspection */ TemplateItemOption $templateItemOption)
    {
        return ! is_null($user->role()->allowContent()->first());
    }

    /**
     * Determine whether the user can delete the templateItemOption.
     *
     * @param  User  $user
     * @param  TemplateItemOption  $templateItemOption
     * @return mixed
     */
    public function delete(User $user, /** @noinspection PhpUnusedParameterInspection */ TemplateItemOption $templateItemOption)
    {
        if ($user->isAdministrator()) {
            return ! is_null($user->role()->allowStructure()->first());
        } else {
            return false;
        }
    }
}
