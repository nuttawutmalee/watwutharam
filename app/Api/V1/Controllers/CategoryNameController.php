<?php

namespace App\Api\V1\Controllers;

use App\Api\Models\CategoryName;
use Illuminate\Http\Request;

class CategoryNameController extends BaseController
{
    /**
     * CategoryNameController constructor.
     */
    function __construct()
    {
        $this->idName = (new CategoryName)->getKeyName();
    }

    /**
     * Return all category names
     *
     * @param Request $request
     * @return mixed
     */
    public function getCategoryNames(/** @noinspection PhpUnusedParameterInspection */ Request $request) {
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(CategoryName::all());
    }
}

