<?php

namespace App\Api\V1\Controllers;

use App\Api\Models\CmsRole;
use Illuminate\Http\Request;

class CmsRoleController extends BaseController
{
    /**
     * CmsRoleController constructor.
     */
    function __construct()
    {
        $this->idName = (new CmsRole)->getKeyName();
    }

    /**
     * Return all roles
     *
     * @param Request $request
     * @return mixed
     */
    public function getRoles(/** @noinspection PhpUnusedParameterInspection */ Request $request) {
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(CmsRole::all());
    }
}

