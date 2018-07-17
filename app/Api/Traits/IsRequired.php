<?php

namespace App\Api\Traits;
use \Illuminate\Database\Eloquent\Builder;

/**
 * Class IsRequired
 *
 * @package App\Api\Traits
 *
 * @traitUses \Illuminate\Database\Eloquent\Model
 *
 * @method static Builder isRequired()
 *
 * @method static Builder isNotRequired()
 */
trait IsRequired
{
    /**
     * Scopes
     */

    /**
     * Return an eloquent query scope whether is_required equals true
     *
     * @param $query
     * @return mixed|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsRequired($query)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        return $query->where('is_required', true);
    }

    /**
     * Return an eloquent query scope whether is_required equals false
     *
     * @param $query
     * @return mixed|\Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsNotRequired($query)
    {
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        return $query->where('is_required', false);
    }

    /**
     * Mutations
     */

    /**
     * Set is_required as a boolean
     *
     * @param $value
     */
    public function setIsRequiredAttribute($value)
    {
        $this->attributes['is_required'] = to_boolean($value);
    }
}