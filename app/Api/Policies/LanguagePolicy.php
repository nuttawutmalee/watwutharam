<?php

namespace App\Api\Policies;

use App\Api\Models\Language;
use App\Api\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LanguagePolicy
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
     * Determine whether the user can create languages.
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
     * Determine whether the user can update the language.
     *
     * @param  User $user
     * @param  Language  $language
     * @return mixed
     */
    public function update(User $user, /** @noinspection PhpUnusedParameterInspection */ Language $language)
    {
        return ! is_null($user->role()->allowContent()->first());
    }

    /**
     * Determine whether the user can delete the language.
     *
     * @param  User  $user
     * @param  Language  $language
     * @return mixed
     */
    public function delete(User $user, /** @noinspection PhpUnusedParameterInspection */ Language $language)
    {
        if ($user->isAdministrator()) {
            return ! is_null($user->role()->allowStructure()->first());
        } else {
            return false;
        }
    }
}
