<?php

namespace App\Api\Models;

use App\Api\Traits\Category;
use App\Api\Traits\InheritComponentOptions;
use App\Api\Traits\IsActive;
use App\Api\Traits\IsVisible;
use Rutorika\Sortable\SortableTrait;

/**
 * Class GlobalItem
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
 * @property int $display_order
 *
 * @property bool $is_active
 *
 * @property bool $is_visible
 *
 * @property string $site_id
 *
 * @property string $component_id
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property int $count_page_items
 *
 * @property Site $site
 *
 * @property GlobalItemOption|GlobalItemOption[] $globalItemOptions
 *
 * @property Component $component
 *
 * @property CategoryName|CategoryName[] $categoryNames
 *
 * @property GlobalItem $parent
 *
 * @property GlobalItem|GlobalItem[] $children
 *
 * @property PageItem|PageItem[] $pageItems
 */
class GlobalItem extends BaseModel
{
    use IsActive, IsVisible, Category, InheritComponentOptions, SortableTrait;

    /**
     * Sortable field
     *
     * @var string
     */
    protected static $sortableField = 'display_order';

    /**
     * Sortable group field
     *
     * @var string
     */
    protected static $sortableGroupField = 'site_id';

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
        'site_id',
        'component_id',
        'is_visible'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_visible' => 'boolean',
        'display_order' => 'integer'
    ];

    /**
     * @var array
     */
    protected $with = [
        'site',
        'component'
    ];

    /**
     * @var array
     */
    protected $appends = [
        'count_page_items'
    ];

    /**
     * GlobalItem constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->optionRelationship = 'globalItemOptions';
    }

    /**
     * Relationships
     */

    /**
     * Return a relationship wth a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Site
     */
    public function site()
    {
        return $this->belongsTo('App\Api\Models\Site', 'site_id');
    }

    /**
     * Return a relationship with global item options
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|GlobalItemOption|GlobalItemOption[]
     */
    public function globalItemOptions()
    {
        return $this->hasMany('App\Api\Models\GlobalItemOption', 'global_item_id');
    }

    /**
     * Return a relationship with a component
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Component
     */
    public function component()
    {
        return $this->belongsTo('App\Api\Models\Component', 'component_id');
    }

    /**
     * Return a relationship with category names
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|CategoryName|CategoryName[]
     */
    public function categoryNames()
    {
        return $this
            ->belongsToMany('App\Api\Models\CategoryName', 'category_name_item_mappings', 'item_id', 'category_name_id')
            ->withTimestamps();
    }

    /**
     * Return a relationship with page items
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|PageItem[]|PageItem
     */
    public function pageItems()
    {
        return $this->hasMany('App\Api\Models\PageItem', 'global_item_id');
    }

    /**
     * Custom Functions
     */

    /**
     * Return amount of page items
     *
     * @return int
     */
    public function getCountPageItemsAttribute()
    {
        return $this->pageItems()->count();
    }

    /**
     * Overrides
     */

    /**
     * Override delete function
     *
     * @param bool $cascadeGlobalItemOption
     * @param bool $cascadeCategoryNameMappings
     * @param bool $cascadePageItem
     * @return bool|null
     */
    public function delete($cascadeGlobalItemOption = true, $cascadeCategoryNameMappings = true, $cascadePageItem = false)
    {
        if ($cascadeGlobalItemOption) {
            if (count($this->globalItemOptions) > 0) {
                foreach ($this->globalItemOptions as $globalItemOption) {
                    $globalItemOption->delete();
                }
            }
        }

        if ($cascadeCategoryNameMappings) {
            $this->detachOptionCategoryNames();
        }

        if ($cascadePageItem) {
            if (count($this->pageItems) > 0) {
                foreach ($this->pageItems as $pageItem) {
                    $pageItem->delete();
                }
            }
        } else {
            $this->pageItems()->update(['global_item_id' => null]);
        }

        return parent::delete();
    }
}
