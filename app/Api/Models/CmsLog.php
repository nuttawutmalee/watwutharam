<?php

namespace App\Api\Models;

use App\Api\Constants\LogConstants;
use Illuminate\Database\Eloquent\Builder;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class CmsLog
 *
 * @package App\Api\Models
 *
 * @property string $id
 *
 * @property string $action
 *
 * @property string $log_data
 *
 * @property string $updated_by
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 */
class CmsLog extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'action',
        'log_data',
        'updated_by',
    ];

    /**
     * Save a log
     *
     * @param $data
     * @param $action
     * @return Builder
     */
    public static function log($data, $action)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        if ($current_token = JWTAuth::getToken()) {
            /** @noinspection PhpUndefinedMethodInspection */
            $auth_user = JWTAuth::parseToken()->authenticate();
        } else {
            $auth_user = LogConstants::SYSTEM;
        }

        return self::create([
            'action' => $action,
            'log_data' => json_encode($data),
            'updated_by' => $auth_user
        ]);
    }
}
