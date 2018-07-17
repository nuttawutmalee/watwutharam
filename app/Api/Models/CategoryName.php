<?php

namespace App\Api\Models;

/**
 * Class CategoryName
 *
 * @package App\Api\Models
 *
 * @property string $id
 *
 * @property string $name
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 */
class CategoryName extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];
}
