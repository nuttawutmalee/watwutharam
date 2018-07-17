<?php

namespace App\Api\Models;

use App\Api\Traits\Uuids;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
/** @noinspection PhpHierarchyChecksInspection */

/**
 * Class BaseModel
 *
 * @package App\Api\Models
 *
 * @method static Builder find($id, array $columns = ['*'])
 *
 * @method static Builder firstOrCreate(array $attributes, array $values = array())
 *
 * @method static Builder findOrFail($id, $columns = ['*'])
 *
 * @method static Builder create(array $attributes = [])
 *
 * @method static Builder where(string|array $column, string $operator = null, mixed $value = null, string $boolean = 'and')
 *
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 *
 * @method static Builder whereHas(string $relation, $callback = null, string $operator = '>=', int $count = 1)
 *
 * @method static Builder whereNotNull($column, $boolean = 'and')
 *
 * @method static Builder|Model with($relations)
 *
 * @method static Builder orderBy(string $column, string $direction = 'asc')
 *
 * @method Model load($relations)
 *
 * @method array toArray()
 *
 * @method get()
 *
 * @method $this|Model|null first()
 *
 * @method $this|Model|null last()
 *
 * @method static void truncate()
 */
abstract class BaseModel extends Model
{
    use Notifiable, Uuids;

    /**
     * Disable auto-increment key
     *
     * @var bool
     */
    public $incrementing = false;
}
