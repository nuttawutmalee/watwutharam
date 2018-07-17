<?php

namespace App\Api\Models;

/**
 * Class Component
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
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property int $count_template_items
 *
 * @property int $count_page_items
 *
 * @property int $count_global_items
 *
 * @property int $count_inheritances
 *
 * @property ComponentOption|ComponentOption[] $componentOptions
 *
 * @property TemplateItem|TemplateItem[] $templateItems
 *
 * @property PageItem|PageItem[] $pageItems
 *
 * @property GlobalItem|GlobalItem[] $globalItems
 */
class Component extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'variable_name',
        'description'
    ];

    /**
     * @var array
     */
    protected $appends = [
        'count_template_items',
        'count_page_items',
        'count_global_items',
        'count_inheritances'
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with component options
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|ComponentOption|ComponentOption[]
     */
    public function componentOptions()
    {
        return $this->hasMany('App\Api\Models\ComponentOption', 'component_id');
    }

    /**
     * Return a relationship with template items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|TemplateItem[]|TemplateItem
     */
    public function templateItems()
    {
        return $this->hasMany('App\Api\Models\TemplateItem', 'component_id');
    }

    /**
     * Return a relationship with page items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|PageItem[]|PageItem
     */
    public function pageItems()
    {
        return $this->hasMany('App\Api\Models\PageItem', 'component_id');
    }

    /**
     * Return a relationship with global items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|GlobalItem[]|GlobalItem
     */
    public function globalItems()
    {
        return $this->hasMany('App\Api\Models\GlobalItem', 'component_id');
    }

    /**
     * Custom Functions
     */

    /**
     * @return int
     */
    public function getCountTemplateItemsAttribute()
    {
        return $this->templateItems()->count();
    }

    /**
     * @return int
     */
    public function getCountPageItemsAttribute()
    {
        return $this->pageItems()->count();
    }

    /**
     * @return int
     */
    public function getCountGlobalItemsAttribute()
    {
        return $this->globalItems()->count();
    }

    /**
     * @return int
     */
    public function getCountInheritancesAttribute()
    {
        return $this->templateItems()->count() + $this->pageItems()->count() + $this->globalItems()->count();
    }

    /**
     * Overrides
     */

    /**
     * Override delete function
     *
     * @param bool $cascadeComponentOption
     * @param bool $cascadeGlobalItem
     * @param bool $cascadeTemplateItem
     * @param bool $cascadePageItem
     * @return bool|null
     */
    public function delete($cascadeComponentOption = true, $cascadeGlobalItem = false, $cascadeTemplateItem = false, $cascadePageItem = false)
    {
        if ($cascadeComponentOption) {
            if (count($this->componentOptions) > 0) {
                foreach ($this->componentOptions as $componentOption) {
                    $componentOption->delete();
                }
            }
        }

        if ($cascadeGlobalItem) {
            if (count($this->globalItems) > 0) {
                foreach ($this->globalItems as $globalItem) {
                    $globalItem->delete();
                }
            }
        } else {
            $this->globalItems()->update(['component_id' => null]);
        }

        if ($cascadeTemplateItem) {
            if (count($this->templateItems) > 0) {
                foreach ($this->templateItems as $templateItem) {
                    $templateItem->delete();
                }
            }
        } else {
            $this->templateItems()->update(['component_id' => null]);
        }

        if ($cascadePageItem) {
            if (count($this->pageItems) > 0) {
                foreach ($this->pageItems as $pageItem) {
                    $pageItem->delete();
                }
            }
        } else {
            $this->pageItems()->update(['component_id' => null]);
        }

        return parent::delete();
    }
}
