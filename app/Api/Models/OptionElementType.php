<?php

namespace App\Api\Models;

/**
 * Class OptionElementType
 *
 * @package App\Api\Models
 *
 * @property string $id
 *
 * @property string $item_id
 *
 * @property string $item_type
 *
 * @property string $element_type
 *
 * @property bool $element_value
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property ComponentOption|GlobalItemOption|PageItemOption|TemplateItemOption|ComponentOption[]|GlobalItemOption[]|PageItemOption[]|TemplateItemOption[] $item
 */
class OptionElementType extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'item_id',
        'item_type',
        'element_type',
        'element_value'
    ];

    /**
     * Get all of the owning item models
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo|ComponentOption|GlobalItemOption|PageItemOption|TemplateItemOption|ComponentOption[]|GlobalItemOption[]|PageItemOption[]|TemplateItemOption[]
     */
    public function item()
    {
        return $this->morphTo();
    }
}
