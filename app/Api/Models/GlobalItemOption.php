<?php

namespace App\Api\Models;

use App\Api\Traits\IsActive;
use App\Api\Traits\IsRequired;
use App\Api\Traits\IsVisible;
use App\Api\Traits\OptionValue;

/**
 * Class GlobalItemOption
 *
 * @package App\Api\Models
 *
 * @property string $id
 *
 * @property string $name
 *
 * @property string $variable_name
 *
 * @property string $description
 *
 * @property bool $is_required
 *
 * @property bool $is_active
 *
 * @property bool $is_visible
 *
 * @property string $global_item_id
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property GlobalItem globalItem
 *
 * @property OptionElementType $elementType
 *
 * @property SiteTranslation|SiteTranslation[] $siteTranslations
 *
 * @property GlobalItemOptionString $string
 *
 * @property GlobalItemOptionDate $date
 *
 * @property GlobalItemOptionDecimal $decimal
 *
 * @property GlobalItemOptionInteger $integer
 */
class GlobalItemOption extends BaseModel
{
    use OptionValue, IsActive, IsRequired, IsVisible;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'variable_name',
        'description',
        'is_required',
        'is_active',
        'is_visible',
        'global_item_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_required' => 'boolean',
        'is_visible' => 'boolean'
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = [
        'globalItem'
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with a global item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|GlobalItem
     */
    public function globalItem()
    {
        return $this->belongsTo('App\Api\Models\GlobalItem', 'global_item_id');
    }

    /**
     * Return a relationship with an element type
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne|OptionElementType
     */
    public function elementType()
    {
        return $this->morphOne('App\Api\Models\OptionElementType', 'item');
    }

    /**
     * Return a relationship with site translations
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|SiteTranslation|SiteTranslation[]
     */
    public function siteTranslations()
    {
        return $this->morphMany('App\Api\Models\SiteTranslation', 'item');
    }

    /**
     * Option Values
     */

    /**
     * Return a relationship with a global item option string
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|GlobalItemOptionString
     */
    public function string()
    {
        return $this->hasOne('App\Api\Models\GlobalItemOptionString', 'global_item_option_id');
    }

    /**
     * Return a relationship with a global item option integer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|GlobalItemOptionInteger
     */
    public function integer()
    {
        return $this->hasOne('App\Api\Models\GlobalItemOptionInteger', 'global_item_option_id');
    }

    /**
     * Return a relationship with a global item option decimal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|GlobalItemOptionDecimal
     */
    public function decimal()
    {
        return $this->hasOne('App\Api\Models\GlobalItemOptionDecimal', 'global_item_option_id');
    }

    /**
     * Return a relationship with a global item option date
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|GlobalItemOptionDate
     */
    public function date()
    {
        return $this->hasOne('App\Api\Models\GlobalItemOptionDate', 'global_item_option_id');
    }

    /**
     * Overrides
     */

    /**
     * Override delete function
     *
     * @param bool $cascadeOptionValue
     * @param bool $cascadeOptionElementType
     * @param bool $cascadeOptionSiteTranslation
     * @return bool|null
     */
    public function delete($cascadeOptionValue = true, $cascadeOptionElementType = true, $cascadeOptionSiteTranslation = true)
    {
        if ($cascadeOptionValue) {
            $this->deleteOptionValue();
        }

        if ($cascadeOptionElementType) {
            $this->deleteOptionElementType();
        }

        if ($cascadeOptionSiteTranslation) {
            $this->deleteOptionSiteTranslation();
        }

        return parent::delete();
    }

    /**
     * Return a parent site
     *
     * @return null|Site
     */
    public function getParentSite()
    {
        try {
            /** @var GlobalItem $globalItem */
            if ($globalItem = $this->globalItem) {
                /** @var Site $site */
                if ($site = $globalItem->site) {
                    return $site;
                }
            }
        } catch (\Exception $e) {}

        return null;
    }
}
