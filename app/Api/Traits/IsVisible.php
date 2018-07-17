<?php

namespace App\Api\Traits;
use \Illuminate\Database\Eloquent\Builder;

/**
 * Class IsVisible
 *
 * @package App\Api\Traits
 *
 * @traitUses \Illuminate\Database\Eloquent\Model
 *
 * @method static Builder isVisible()
 *
 * @method static Builder isHidden()
 */
trait IsVisible
{
    /**
     * Scopes
     */

    /**
     * Return an eloquent query scope whether is_visible equals true
     *
     * @param $query
     * @return mixed|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsVisible($query)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        return $query->where('is_visible', true);
    }

    /**
     * Return an eloquent query scope whether is_visible equals false
     *
     * @param $query
     * @return mixed|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsHidden($query)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        return $query->where('is_visible', false);
    }

    /**
     * Mutations
     */

    /**
     * Set is_developer as a boolean
     *
     * @param $value
     */
    public function setIsVisibleAttribute($value)
    {
        $this->attributes['is_visible'] = to_boolean($value);
    }
}