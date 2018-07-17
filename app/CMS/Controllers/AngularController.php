<?php

namespace App\CMS\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AngularController extends BaseController
{
    /**
     * AngularController constructor.
     */
    function __construct()
    {
        //
    }

    /**
     * Return index.html
     *
     * @param Request $request
     * @return mixed
     */
    public function index(/** @noinspection PhpUnusedParameterInspection */ Request $request) {
        /** @noinspection PhpUndefinedMethodInspection */
        return File::get(public_path() . '/system/index.html');
    }
}

