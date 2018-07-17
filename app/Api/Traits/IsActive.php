<?php

namespace App\Api\Traits;
use \Illuminate\Database\Eloquent\Builder;

/**
 * Class IsActive
 *
 * @package App\Api\Traits
 *
 * @traitUses \Illuminate\Database\Eloquent\Model
 *
 * @method static Builder isActive()
 *
 * @method static Builder isNotActive()
 */
trait IsActive
{
    /**
     * Scopes
     */

    /**
     * Return an eloquent query scope whether is_active equals true
     *
     * @param $query
     * @return mixed|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsActive($query)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        return $query->where('is_active', true);
    }

    /**
     * Return an eloquent query scope whether is_active equals false
     *
     * @param $query
     * @return mixed|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsInactive($query)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        return $query->where('is_active', false);
    }

    /**
     * Mutations
     */

    /**
     * Set is_developer as a boolean
     *
     * @param $value
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['is_active'] = to_boolean($value);
    }
}