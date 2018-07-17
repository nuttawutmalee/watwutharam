<?php

namespace App\Api\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class CategoryNameItemMapping
 *
 * @package App\Api\Models
 *
 * @property string $item_id
 *
 * @property string $category_name_id
 *
 * @property string $name
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 */
class CategoryNameItemMapping extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id',
        'category_name_id'
    ];
}
