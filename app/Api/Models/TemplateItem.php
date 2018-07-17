<?php

namespace App\Api\Models;

use App\Api\Traits\InheritComponentOptions;
use Rutorika\Sortable\SortableTrait;

/**
 * Class TemplateItem
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
 * @property string $template_id
 *
 * @property string $component_id
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property Component $component
 *
 * @property TemplateItemOption|TemplateItemOption[] $templateItemOptions
 *
 * @property Template $template
 */
class TemplateItem extends BaseModel
{
    use SortableTrait, InheritComponentOptions;

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
    protected static $sortableGroupField = 'template_id';

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
        'component_id',
        'template_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'display_order' => 'integer'
    ];

    /**
     * Eager loading relationships
     *
     * @var array
     */
    protected $with = [
        'template',
        'component'
    ];

    /**
     * TemplateItem constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->optionRelationship = 'templateItemOptions';
    }

    /**
     * Relationships
     */

    /**
     * Return a relationship with a template
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Template
     */
    public function template()
    {
        return $this->belongsTo('App\Api\Models\Template', 'template_id');
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
     * Return a relationship with template item options
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|TemplateItemOption|TemplateItemOption[]
     */
    public function templateItemOptions()
    {
        return $this->hasMany('App\Api\Models\TemplateItemOption', 'template_item_id');
    }

    /**
     * Overrides
     */

    /**
     * Override delete function
     *
     * @param bool $cascadeTemplateItemOption
     * @return bool|null
     */
    public function delete($cascadeTemplateItemOption = true)
    {
        if ($cascadeTemplateItemOption) {
            if (count($this->templateItemOptions) > 0) {
                foreach ($this->templateItemOptions as $templateItemOption) {
                    $templateItemOption->delete();
                }
            }
        }

        return parent::delete();
    }
}
