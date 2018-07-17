<?php

namespace App\Api\Models;

use App\Api\Traits\IsActive;

/**
 * Class RedirectUrl
 *
 * @package App\Api\Models
 *
 * @property string $id
 *
 * @property int $status_code
 *
 * @property string $source_url
 *
 * @property string $destination_url
 *
 * @property bool $is_active
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property Site $site
 */
class RedirectUrl extends BaseModel
{
    use IsActive;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'status_code',
        'source_url',
        'destination_url',
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
     * Auto Lazy-loading
     *
     * @var array
     */
    protected $with = [
        'site'
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Site
     */
    public function site()
    {
        return $this->belongsTo('App\Api\Models\Site', 'site_id');
    }
}
