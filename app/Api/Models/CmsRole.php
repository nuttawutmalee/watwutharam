<?php

namespace App\Api\Models;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class CmsRole
 *
 * @package App\Api\Models
 *
 * @property string $id
 *
 * @property string $name
 *
 * @property bool $is_developer
 *
 * @property bool $allow_structure
 *
 * @property bool $allow_content
 *
 * @property bool $allow_user
 *
 * @property string $updated_by
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property User|User[] $users
 *
 * @method static Builder isDeveloper($flag = true)
 *
 * @method static Builder allowStructure($flag = true)
 *
 * @method static Builder allowContent($flag = true)
 *
 * @method static Builder allowUser($flag = true)
 */
class CmsRole extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'is_developer',
        'allow_structure',
        'allow_content',
        'allow_user',
        'updated_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_developer' => 'boolean',
        'allow_structure' => 'boolean',
        'allow_content' => 'boolean',
        'allow_user' => 'boolean'
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|User|User[]
     */
    public function users()
    {
        return $this->hasMany('App\Api\Models\User', 'role_id');
    }

    /**
     * Mutations
     */

    /**
     * Set is_developer as a boolean
     *
     * @param $value
     */
    public function setIsDeveloperAttribute($value)
    {
        $this->attributes['is_developer'] = to_boolean($value);
    }

    /**
     * Set allow_structure as a boolean
     *
     * @param $value
     */
    public function setAllowStructureAttribute($value)
    {
        $this->attributes['allow_structure'] = to_boolean($value);
    }

    /**
     * Set allow_content as a boolean
     *
     * @param $value
     */
    public function setAllowContentAttribute($value)
    {
        $this->attributes['allow_content'] = to_boolean($value);
    }

    /**
     * Set allow_user as a boolean
     *
     * @param $value
     */
    public function setAllowUserAttribute($value)
    {
        $this->attributes['allow_user'] = to_boolean($value);
    }

    /**
     * Scopes
     */

    /**
     * Return an eloquent query scope to check if user is a developer
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $flag
     * @return mixed
     */
    public function scopeIsDeveloper($query, $flag = true)
    {
        return $query->where('is_developer', $flag);
    }

    /**
     * Return an eloquent query scope to check allow_structure
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $flag
     * @return mixed
     */
    public function scopeAllowStructure($query, $flag = true)
    {
        return $query->where('allow_structure', $flag);
    }

    /**
     * Return an eloquent query scope to check allow_content
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $flag
     * @return mixed
     */
    public function scopeAllowContent($query, $flag = true)
    {
        return $query->where('allow_content', $flag);
    }

    /**
     * Return an eloquent query scope to check allow_user
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $flag
     * @return mixed
     */
    public function scopeAllowUser($query, $flag = true)
    {
        return $query->where('allow_user', $flag);
    }
}
