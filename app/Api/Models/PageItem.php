<?php

namespace App\Api\Models;

use App\Api\Traits\InheritComponentOptions;
use App\Api\Traits\IsActive;
use App\Api\Traits\IsRequired;
use App\Api\Traits\IsVisible;
use Rutorika\Sortable\SortableTrait;

/**
 * Class PageItem
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
 * @property bool $is_required
 *
 * @property bool $is_visible
 *
 * @property string $page_id
 *
 * @property string $component_id
 *
 * @property string $global_item_id
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property Component $component
 *
 * @property Page $page
 *
 * @property PageItemOption|PageItemOption[] $pageItemOptions
 *
 * @property GlobalItem $globalItem
 *
 */
class PageItem extends BaseModel
{
    use IsActive, IsRequired, IsVisible, SortableTrait, InheritComponentOptions;

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = [
        'page'
    ];

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
    protected static $sortableGroupField = 'page_id';

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
        'display_order',
        'is_required',
        'is_active',
        'is_visible',
        'component_id',
        'global_item_id',
        'page_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_required' => 'boolean',
        'is_visible' => 'boolean',
        'display_order' => 'integer'
    ];

    /**
     * Eager loading relationships
     *
     * @var array
     */
    protected $with = [
        'page',
        'component',
        'globalItem'
    ];

    /**
     * PageItem constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->optionRelationship = 'pageItemOptions';
    }

    /**
     * Relationships
     */

    /**
     * Return a relationship with a page
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Page
     */
    public function page()
    {
        return $this->belongsTo('App\Api\Models\Page', 'page_id');
    }

    /**
     * Return a relationship a component
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Component
     */
    public function component()
    {
        return $this->belongsTo('App\Api\Models\Component', 'component_id');
    }

    /**
     * Return a relationship with page item options
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|PageItemOption|PageItemOption[]
     */
    public function pageItemOptions()
    {
        return $this->hasMany('App\Api\Models\PageItemOption', 'page_item_id');
    }

    /**
     * Return a relationship with a global item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function globalItem()
    {
        return $this->belongsTo('App\Api\Models\GlobalItem', 'global_item_id');
    }

    /**
     * Overrides
     */

    /**
     * Override delete function
     *
     * @param bool $cascadePageItemOption
     * @return bool|null
     */
    public function delete($cascadePageItemOption = true)
    {
        if ($cascadePageItemOption) {
            if (count($this->pageItemOptions) > 0) {
                foreach ($this->pageItemOptions as $pageItemOption) {
                    $pageItemOption->delete();
                }
            }
        }

        return parent::delete();
    }
}