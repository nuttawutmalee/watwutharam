<?php

namespace App\Api\Models;

use App\Api\Constants\OptionValueConstants;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
/** @noinspection PhpHierarchyChecksInspection */

/**
 * Class GlobalItemOptionDecimal
 *
 * @package App\Api\Models
 *
 * @property string $global_item_option_id
 *
 * @property double $option_value
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property GlobalItemOption $option
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
class GlobalItemOptionDecimal extends Model
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
    public $option_type = OptionValueConstants::DECIMAL;

    /**
     * Primary key
     *
     * @var string
     */
    protected $primaryKey = 'global_item_option_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'global_item_option_id',
        'option_value'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'option_value' => 'double'
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with a global item option
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|GlobalItemOption
     */
    public function option()
    {
        return $this->belongsTo('App\Api\Models\GlobalItemOption', 'global_item_option_id');
    }
}
