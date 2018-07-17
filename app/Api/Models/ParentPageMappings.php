<?php

namespace App\Api\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class ParentPageMappings
 *
 * @package App\Api\Models
 *
 * @property string $page_id
 *
 * @property string $parent_id
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 */
class ParentPageMappings extends Pivot
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'page_id',
        'parent_id'
    ];
}
