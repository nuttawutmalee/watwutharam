<?php

namespace App\CMS\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Throw an exception if the incoming request is invalid against the rules
     *
     * @param $data
     * @param array $rules
     * @throws \Exception
     */
    protected function guardAgainstInvalidateRequest($data, $rules = [])
    {
        if ( ! empty($rules)) {
            /** @var \Illuminate\Validation\Validator $validator */
            /** @noinspection PhpUndefinedMethodInspection */
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $messages = [];
                foreach ($errors->all() as $message) {
                    array_push($messages, $message);
                }
                throw new \Exception(join(', ', $messages), BaseResponse::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
