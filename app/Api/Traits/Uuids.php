<?php
namespace App\Api\Traits;

use App\Api\Constants\LogConstants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Webpatser\Uuid\Uuid;

/**
 * Class Uuids
 *
 * @package App\Api\Traits
 *
 * @traitUses \Illuminate\Database\Eloquent\Model
 */
trait Uuids
{
    /**
     * Trigger Model boot function
     */
    protected static function boot()
    {
        /** @noinspection PhpUndefinedClassInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        parent::boot();
    }

    /**
     * Generate uuid
     */
    public static function bootUuids()
    {
        static::creating(function (Model $model) {
            if ( ! empty($model->getAttribute(LogConstants::RECOVERABLE_ID)) && $model->getAttribute(LogConstants::RECOVERABLE_ID)) {
                $model->setAttribute($model->getKeyName(), $model->getAttribute(LogConstants::RECOVERABLE_ID));
                unset($model->{LogConstants::RECOVERABLE_ID});
            }

            if (empty($model->getAttribute($model->getKeyName()))) {
                $model->setAttribute($model->getKeyName(), Uuid::generate(4)->__toString());
            }
        });

        static::saving(function (Model $model) {
            $original_uuid = $model->getOriginal($model->getKeyName());
            if ($original_uuid !== $model->getAttribute($model->getKeyName())) {
                $model->setAttribute($model->getKeyName(), $original_uuid);
            }
        });
    }

    /**
     * Return an eloquent query scope whether the uuid is the same as an input uuid or not
     *
     * @param $query
     * @param $uuid
     * @param string $keyName
     * @param bool $first
     * @return mixed
     */
    public function scopeUuid($query, $uuid, $keyName = 'id', $first = true)
    {
        if ( ! is_string($uuid) || (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid) !== 1)) {
            throw (new ModelNotFoundException)->setModel(get_class($this));
        }

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $search = $query->where($keyName, $uuid);

        return $first ? $search->firstOrFail() : $search;
    }
}