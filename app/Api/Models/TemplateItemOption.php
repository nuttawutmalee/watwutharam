<?php

namespace App\Api\Models;

use App\Api\Traits\IsActive;
use App\Api\Traits\IsRequired;
use App\Api\Traits\OptionValue;

/**
 * Class TemplateItemOption
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
 * @property string $template_item_id
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property OptionElementType $elementType
 *
 * @property SiteTranslation|SiteTranslation[] $siteTranslations
 *
 * @property TemplateItemOptionString $string
 *
 * @property TemplateItemOptionDate $date
 *
 * @property TemplateItemOptionDecimal $decimal
 *
 * @property TemplateItemOptionInteger $integer
 *
 * @property TemplateItem $templateItem
 */
class TemplateItemOption extends BaseModel
{
    use OptionValue, IsActive, IsRequired;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'variable_name', 'description', 'is_active', 'is_required', 'template_item_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_required' => 'boolean',
    ];
    
    /**
     * Relationships
     */

    /**
     * Return a relationship with a template item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|TemplateItem
     */
    public function templateItem()
    {
        return $this->belongsTo('App\Api\Models\TemplateItem', 'template_item_id');
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|SiteTranslation
     */
    public function siteTranslations()
    {
        return $this->morphMany('App\Api\Models\SiteTranslation', 'item');
    }

    /**
     * Option Values
     */

    /**
     * Return a relationship with a template item option string
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|TemplateItemOptionString
     */
    public function string()
    {
        return $this->hasOne('App\Api\Models\TemplateItemOptionString', 'template_item_option_id');
    }

    /**
     * Return a relationship with a template item option integer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|TemplateItemOptionInteger
     */
    public function integer()
    {
        return $this->hasOne('App\Api\Models\TemplateItemOptionInteger', 'template_item_option_id');
    }

    /**
     * Return a relationship with a template item option decimal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|TemplateItemOptionDecimal
     */
    public function decimal()
    {
        return $this->hasOne('App\Api\Models\TemplateItemOptionDecimal', 'template_item_option_id');
    }

    /**
     * Return a relationship with a template item option date
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|TemplateItemOptionDate
     */
    public function date()
    {
        return $this->hasOne('App\Api\Models\TemplateItemOptionDate', 'template_item_option_id');
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
            /** @var TemplateItem $templateItem */
            if ($templateItem = $this->templateItem) {
                /** @var Template $template */
                if ($template = $templateItem->template) {
                    if ($site = $template->site) {
                        return $site;
                    }
                }
            }
        } catch (\Exception $e) {}

        return null;
    }
}
