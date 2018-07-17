<?php

namespace App\Api\Policies;

use App\Api\Models\Template;
use App\Api\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TemplatePolicy
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
     * Determine whether the user can create templates.
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
     * Determine whether the user can update the template.
     *
     * @param  User $user
     * @param  Template  $template
     * @return mixed
     */
    public function update(User $user, /** @noinspection PhpUnusedParameterInspection */ Template $template)
    {
        return ! is_null($user->role()->allowContent()->first());
    }

    /**
     * Determine whether the user can delete the template.
     *
     * @param  User  $user
     * @param  Template  $template
     * @return mixed
     */
    public function delete(User $user, /** @noinspection PhpUnusedParameterInspection */ Template $template)
    {
        if ($user->isAdministrator()) {
            return ! is_null($user->role()->allowStructure()->first());
        } else {
            return false;
        }
    }
}
