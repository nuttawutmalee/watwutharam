<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Models\CmsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ReflectionMethod;

class LogController extends BaseController
{
    /**
     * CategoryNameController constructor.
     */
    function __construct()
    {
        $this->idName = (new CmsLog)->getKeyName();
    }

    /**
     * Return log size in megabytes
     *
     * @param Request $request
     * @return mixed
     */
    public function getLogSize(/** @noinspection PhpUnusedParameterInspection */Request $request)
    {
        $data = [
            'size' => 0,
            'type' => 'MB'
        ];

        if ($size = get_db_table_size('cms_logs')) {
            $data['size'] = number_format(get_db_table_size('cms_logs')/(1024*1024), 2);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($data);
    }

    /**
     * Return all recoverable items
     *
     * @param Request $request
     * @return mixed
     */
    public function listRecoverableItems(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @var CmsLog[]|\Illuminate\Support\Collection  $logs */
        $logs = CmsLog::where('updated_by', '<>', LogConstants::SYSTEM)
            ->where(function ($query) {
                /** @var \Illuminate\Database\Eloquent\Builder $query */
                $query->where('action', 'like', '%_BEFORE_%')
                    ->orWhere('action', 'like', '%_CREATED');
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($logs);
    }

    /**
     * Recover
     *
     * @param Request $request
     * @return mixed
     */
    public function recoverRecoverableItems(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.*' => 'string|exists:cms_logs,' . $this->idName
        ]);

        $ids = $request->input('data', []);

        if ( ! empty($ids)) {
            /** @var CmsLog[]|\Illuminate\Support\Collection  $logs */
            $logs = CmsLog::whereIn($this->idName, $ids)
                ->where(function ($query) {
                    /** @var \Illuminate\Database\Eloquent\Builder $query */
                    $query->where('action', 'like', '%BEFORE%')
                        ->orWhere('action', 'like', '%CREATED%');
                })
                ->orderBy('updated_at', 'desc')
                ->get();

            if ( ! empty($logs)) {
                $logs = collect($logs)
                    ->sort(function ($a, $b) {
                        /**
                         * @var CmsLog $a
                         * @var CmsLog $b
                         */
                        if (preg_match('/DELETED$/', $a->action) && preg_match('/(UPDATED|CREATED)$/', $b->action)) {
                            return -1;
                        } else if (preg_match('/DELETED$/', $b->action) && preg_match('/(UPDATED|CREATED)$/', $a->action)) {
                            return 1;
                        } else if (preg_match('/UPDATED$/', $a->action) && preg_match('/CREATED$/', $b->action)) {
                            return -1;
                        } else if (preg_match('/UPDATED$/', $b->action) && preg_match('/CREATED$/', $a->action)) {
                            return 1;
                        } else {
                            return 0;
                        }
                    })
                    ->all();

                /** @noinspection PhpUndefinedMethodInspection */
                DB::transaction(function () use ($logs) {
                    foreach ($logs as $log) {
                        /** @var CmsLog $log */
                        $this->authorizeForUser($this->auth->user(), 'update', $log);

                        if ( isset($log->action) && ! empty($log->action)
                            && isset($log->log_data) && ! empty($log->log_data)) {

                            if (preg_match('/_CREATED$/', $log->action)) {
                                list($modelName, ) = explode('_CREATED', $log->action);
                                $operation = 'CREATED';
                            } else {
                                list($modelName, $operation) = explode('_BEFORE_', $log->action);
                            }

                            $modelName = preg_replace('/_/', '', ucwords(strtolower($modelName), '_'));

                            $modelName = "App\\Api\\Models\\$modelName";

                            if ( ! class_exists($modelName)) {
                                throw new \Exception(ErrorMessageConstants::CLASS_NOT_FOUND);
                            }

                            /** @var \Illuminate\Database\Eloquent\Model $model */
                            $model = (new $modelName);
                            $modelKeyName = $model->getKeyName();

                            $logData = json_recursive_decode($log->log_data);

                            try {
                                switch ($operation) {
                                    case 'CREATED': //CREATED -> DELETE
                                        $id = $logData->{$modelKeyName};

                                        //DO NOT CASCADE
                                        $fct = new ReflectionMethod($modelName, 'delete');
                                        $numberOfParameters = $fct->getNumberOfParameters();

                                        /** @var \Illuminate\Database\Eloquent\Model|\App\Api\Models\BaseModel $modelName */
                                        $object = $modelName::findOrFail($id);
                                        $handler = array($object, 'delete');

                                        if ($numberOfParameters > 0) {
                                            $params = array_pad([], $numberOfParameters, false);
                                            if (is_callable($handler)) call_user_func_array($handler, $params);
                                        } else {
                                            $object->delete();
                                        }
                                        break;
                                    case 'DELETED': //DELETED -> CREATE
                                        $data = collect($logData)->toArray();
                                        if ( ! empty($data)) {
                                            /** @var \Illuminate\Database\Eloquent\Model|\App\Api\Models\BaseModel $object */
                                            $object = new $modelName();
                                            foreach ($data as $key => $value) {
                                                $object->{$key} = $value;
                                            }
                                            $object->{LogConstants::RECOVERABLE_ID} = $object->{$modelKeyName};
                                            $object->save();
                                        }
                                        break;
                                    case 'UPDATED': //UPDATED -> UPDATE
                                        $id = $logData->{$modelKeyName};

                                        /** @var \Illuminate\Database\Eloquent\Model|\App\Api\Models\BaseModel $modelName */
                                        $modelName::findOrFail($id)->update(collect($logData)->toArray());
                                        break;
                                    default: //NONE -> ERROR
                                        throw new \Exception(ErrorMessageConstants::UNRECOVERABLE_ITEM);
                                        break;
                                }
                            } catch (\Exception $e) {
                                throw new \Exception($e->getMessage(), $e->getCode());
                            } finally {
                                //DELETE A LOG (CLEAR DATABASE DISK SPACE)
                                $log->delete();
                            }
                        }
                    }
                });
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(true);
    }

    /**
     * Flush all CMS logs
     *
     * @param Request $request
     * @return mixed
     */
    public function flush(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'except_submission' => 'sometimes|is_boolean'
        ]);

        $exceptSubmission = $request->exists('except_submission') ? to_boolean($request->get('except_submission', true)) : true;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($exceptSubmission) {
            if ($exceptSubmission) {
                CmsLog::where('action', '!=', LogConstants::FORM_SUBMIT)->delete();
            } else {
                CmsLog::truncate();
            }

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                DB::select('OPTIMIZE TABLE ' . (new CmsLog)->getTable());
            } catch (\Exception $exception) {}
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(true);
    }
}