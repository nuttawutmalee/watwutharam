<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Models\ComponentOption;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\PageItemOption;
use App\Api\Models\TemplateItemOption;
use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class BaseController extends Controller
{
    use Helpers;

    /**
     * Default id key name
     *
     * @var string
     */
    protected $idName = 'id';

    /**
     * Throw an exception if incoming request is invalid against the rules
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

    /**
     * Throw an exception if uploaded file is already uploaded
     *
     * @param $option
     * @param $hasFile
     * @throws \Exception
     */
    protected function guardAgainstStrictUploadConstraint($option, $hasFile)
    {
        if ( ! $option instanceof ComponentOption &&
             ! $option instanceof TemplateItemOption &&
             ! $option instanceof PageItemOption &&
             ! $option instanceof GlobalItemOption) {
            throw new \Exception(ErrorMessageConstants::WRONG_MODEL);
        }

        $doesOptionHaveAFile = $option->hasOptionUploadedFile();

        if ($doesOptionHaveAFile && ! $hasFile) {
            throw new \Exception(ErrorMessageConstants::FILE_UPLOAD_IS_REQUIRED);
        }

        if ( ! $doesOptionHaveAFile && $hasFile) {
            throw new \Exception(ErrorMessageConstants::FILE_UPLOAD_NOT_ALLOWED);
        }
    }

    /**
     * Guard against inactive self and its predecessors
     *
     * @param $option
     * @return bool
     * @throws \Exception
     */
    protected function guardAgainstInactiveItemOptionParentsUntilSite($option)
    {
        if (! $option instanceof TemplateItemOption &&
            ! $option instanceof PageItemOption &&
            ! $option instanceof GlobalItemOption) {
            throw new \Exception(ErrorMessageConstants::WRONG_MODEL);
        }

        if (is_null($option)) throw new ModelNotFoundException();

        if ( ! $option->is_active) throw new \Exception(ErrorMessageConstants::INACTIVE_MODEL);

        if ($option instanceof PageItemOption) {
            if ($pageItem = $option->pageItem) {
                if ( ! $pageItem->is_active) throw new \Exception(ErrorMessageConstants::INACTIVE_MODEL);
                if ($page = $pageItem->page) {
                    if ( ! $page->is_active) throw new \Exception(ErrorMessageConstants::INACTIVE_MODEL);
                    if ($template = $page->template) {
                        if ($site = $template->site) {
                            if ( ! $site->is_active) throw new \Exception(ErrorMessageConstants::INACTIVE_MODEL);
                            return true;
                        }
                    }
                }
            }
        } else if ($option instanceof TemplateItemOption) {
            if ($templateItem = $option->templateItem) {
                if ( ! $templateItem->is_active) throw new \Exception(ErrorMessageConstants::INACTIVE_MODEL);
                if ($template = $templateItem->template) {
                    if ($site = $template->site) {
                        if ( ! $site->is_active) throw new \Exception(ErrorMessageConstants::INACTIVE_MODEL);
                        return true;
                    }
                }
            }
        } else if ($option instanceof GlobalItemOption) {
            if ($globalItem = $option->globalItem) {
                if ( ! $globalItem->is_active) throw new \Exception(ErrorMessageConstants::INACTIVE_MODEL);
                if ($site = $globalItem->site) {
                    if ( ! $site->is_active) throw new \Exception(ErrorMessageConstants::INACTIVE_MODEL);
                    return true;
                }
            }
        }

        throw new ModelNotFoundException();
    }
}
