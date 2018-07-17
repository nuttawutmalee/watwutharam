<?php

namespace App\Api\Models;

use App\Api\Traits\IsActive;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class SiteLanguage
 *
 * @package App\Api\Models
 *
 * @property string $site_id
 *
 * @property string $language_code
 *
 * @property int $display_order
 *
 * @property bool $is_active
 *
 * @property bool $is_main
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property SiteTranslation|SiteTranslation[] $siteTranslations
 */
class SiteLanguage extends Pivot
{
    use IsActive;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'site_id',
        'language_code',
        'is_active',
        'display_order',
        'is_main'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_main' => 'boolean',
        'display_order' => 'integer'
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with site translations
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany|SiteTranslation|SiteTranslation[]
     */
    public function siteTranslations()
    {
        return $this->hasMany('App\Api\Models\SiteTranslation', 'language_code', 'language_code');
    }

    /**
     * Overrides
     */

    /**
     * Override delete function
     *
     * @param bool $cascadeSiteTranslation
     * @return bool|null
     */
    public function delete($cascadeSiteTranslation = true)
    {
        if ($cascadeSiteTranslation) {
            $this->siteTranslations()->delete();
        }

        return parent::delete();
    }

    /**
     * Mutations
     */

    /**
     * Set is_main as a boolean
     *
     * @param $value
     */
    public function setIsMainAttribute($value)
    {
        $this->attributes['is_main'] = to_boolean($value);
    }
}
