<?php

namespace App\Api\Models;

use App\Api\Traits\IsActive;
use App\Api\Traits\IsRequired;
use App\Api\Traits\IsVisible;
use App\Api\Traits\OptionValue;

/**
 * Class PageItemOption
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
 * @property string $page_item_id
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 * 
 * @property PageItem $pageItem
 *
 * @property OptionElementType $elementType
 *
 * @property SiteTranslation|SiteTranslation[] $siteTranslations
 *
 * @property PageItemOptionString $string
 *
 * @property PageItemOptionDate $date
 *
 * @property PageItemOptionDecimal $decimal
 *
 * @property PageItemOptionInteger $integer
 */
class PageItemOption extends BaseModel
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
        'is_active',
        'is_required',
        'is_visible',
        'page_item_id'
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = [
        'pageItem'
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
     * Relationships
     */

    /**
     * Return a relationship with a page item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|PageItem
     */
    public function pageItem()
    {
        return $this->belongsTo('App\Api\Models\PageItem', 'page_item_id');
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
     * Return a relationship with a page item option string
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|PageItemOptionString
     */
    public function string()
    {
        return $this->hasOne('App\Api\Models\PageItemOptionString', 'page_item_option_id');
    }

    /**
     * Return a relationship with a page item option integer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|PageItemOptionInteger
     */
    public function integer()
    {
        return $this->hasOne('App\Api\Models\PageItemOptionInteger', 'page_item_option_id');
    }

    /**
     * Return a relationship with a page item option decimal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|PageItemOptionDecimal
     */
    public function decimal()
    {
        return $this->hasOne('App\Api\Models\PageItemOptionDecimal', 'page_item_option_id');
    }

    /**
     * Return a relationship with a page item option date
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|PageItemOptionString
     */
    public function date()
    {
        return $this->hasOne('App\Api\Models\PageItemOptionDate', 'page_item_option_id');
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
            /**
             * @var PageItemOption $this
             * @var PageItem $pageItem
             */
            if ($pageItem = $this->pageItem) {
                /** @var Page $page */
                if ($page = $pageItem->page) {
                    /** @var Template $template */
                    if ($template = $page->template) {
                        /** @var Site $site */
                        if ($site = $template->site) {
                            return $site;
                        }
                    }
                }
            }
        } catch (\Exception $e) {}

        return null;
    }
}
