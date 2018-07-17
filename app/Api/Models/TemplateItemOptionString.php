<?php

namespace App\Api\Models;

use App\Api\Constants\OptionValueConstants;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
/** @noinspection PhpHierarchyChecksInspection */

/**
 * Class TemplateItemOptionString
 *
 * @package App\Api\Models
 *
 * @property string $template_item_option_id
 *
 * @property string $option_value
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property TemplateItemOption $option
 *
 * @method static Builder find($id, array $columns = ['*'])
 *
 * @method static Builder firstOrCreate(array $attributes, array $values = array())
 *
 * @method static Builder create(array $attributes = [])
 *
 * @method static Builder where(string|array $column, string $operator = null, mixed $value = null, string $boolean = 'and')
 *
 * @method static Builder|Model with($relations)
 *
 * @method Model load($relations)
 *
 * @method array toArray()
 *
 * @method get()
 */
class TemplateItemOptionString extends Model
{
    /**
     * Disable auto-increment key
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Option type
     *
     * @var string
     */
    public $option_type = OptionValueConstants::STRING;

    /**
     * Primary key
     *
     * @var string
     */
    protected $primaryKey = 'template_item_option_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'template_item_option_id',
        'option_value'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'option_value' => 'string'
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with a template item option
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|TemplateItemOption
     */
    public function option()
    {
        return $this->belongsTo('App\Api\Models\TemplateItemOption', 'template_item_option_id');
    }
}
