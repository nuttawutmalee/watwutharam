<?php

namespace App\Api\Policies;

use App\Api\Constants\RoleConstants;
use App\Api\Models\CmsRole;
use App\Api\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
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
            return is_null($user->role()->allowUser()->first()) ? false : null;
        } else {
            return false;
        }
    }

    /**
     * Determine whether the user can create a new user.
     *
     * @param  User  $auth
     * @param  CmsRole $role
     * @return mixed
     */
    public function create(/** @noinspection PhpUnusedParameterInspection */ User $auth, CmsRole $role)
    {
        return $role->name === RoleConstants::EDITORIAL;
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  User  $auth
     * @param  User  $user
     * @return mixed
     */
    public function update(User $auth, User $user)
    {
        $isUpdateItself = $auth->id === $user->id;
        return $isUpdateItself || $user->isEditorial();
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  User  $auth
     * @param  User  $user
     * @return mixed
     */
    public function delete(User $auth, User $user)
    {
        $isDeleteItself = $auth->id === $user->id;
        return $isDeleteItself || $user->isEditorial();
    }
}
