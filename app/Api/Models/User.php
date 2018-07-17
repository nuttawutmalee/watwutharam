<?php

namespace App\Api\Models;

use App\Api\Constants\RoleConstants;
use App\Api\Traits\IsActive;
use App\Api\Traits\Uuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as AuthUser;

/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
/** @noinspection PhpHierarchyChecksInspection */

/**
 * Class User
 *
 * @package App\Api\Models
 *
 * @property string $id
 *
 * @property string $name
 *
 * @property string $email
 *
 * @property string $password
 *
 * @property bool $is_active
 *
 * @property string $remember_token
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property CMSRole $role
 *
 * @method static Builder find($id, array $columns = ['*'])
 *
 * @method static Builder firstOrCreate(array $attributes, array $values = array())
 *
 * @method static Builder findOrFail($id, $columns = ['*'])
 *
 * @method static Builder create(array $attributes = [])
 *
 * @method static Builder where(string|array $column, string $operator = null, mixed $value = null, string $boolean = 'and')
 *
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 *
 * @method static Builder whereHas(string $relation, $callback = null, string $operator = '>=', int $count = 1)
 *
 * @method static Builder|Model with($relations)
 *
 * @method static Builder orderBy(string $column, string $direction = 'asc')
 *
 * @method Model load($relations)
 *
 * @method array toArray()
 *
 * @method get()
 *
 * @method static void truncate()
 */
class User extends AuthUser
{
    use Notifiable, Uuids, IsActive;

    /**
     * Disable auto-increment key
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Table name in the database
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role_id',
        'is_active'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Eager loading relationships
     *
     * @var array
     */
    protected $with = [
        'role'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with a cms role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|CMSRole
     */
    public function role()
    {
        return $this->belongsTo('App\Api\Models\CmsRole', 'role_id');
    }

    /**
     * Mutations
     */

    /**
     * Encrypt password
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }

    /**
     * Custom functions
     */

    /**
     * Return a boolean whether the user is a developer or not
     *
     * @return bool
     */
    public function isDeveloper()
    {
        return ! is_null($this->role()->isDeveloper()->first());
    }

    /**
     * Return a boolean whether the user is an administrator or not
     *
     * @return bool
     */
    public function isAdministrator()
    {
        return ! is_null($this->role()->where('name', RoleConstants::ADMINISTRATOR)->first());
    }

    /**
     * Return a boolean whether the user is an editorial or not
     *
     * @return bool
     */
    public function isEditorial()
    {
        return ! is_null($this->role()->where('name', RoleConstants::EDITORIAL)->first());
    }

}
