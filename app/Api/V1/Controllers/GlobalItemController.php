<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GlobalItemController extends BaseController
{
    /**
     * GlobalItemController constructor.
     */
    function __construct()
    {
        $this->idName = (new GlobalItem)->getKeyName();
    }

    /**
     * Return all global items
     *
     * @param Request $request
     * @return mixed
     */
    public function getGlobalItems(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @var GlobalItem[]|\Illuminate\Support\Collection $globalItems */
        $globalItems = GlobalItem::all();

        $globalItems->transform(function ($item) {
            /** @var GlobalItem $item */
            return $item->withNecessaryData();
        });
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($globalItems->all());
    }

    /**
     * Return a global item by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getGlobalItemById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var GlobalItem $item */
        $item = GlobalItem::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($item->withNecessaryData()->all());
    }

    /**
     * Return all global item options by its parent global item id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getGlobalItemOptionsByGlobalItemId(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var GlobalItem $globalItem */
        $globalItem = GlobalItem::findOrFail($id);

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $globalItemOptions */
        $globalItemOptions = $globalItem->globalItemOptions;

        $globalItemOptions->transform(function ($item) {
            /** @var GlobalItem $item */
            return $item->withNecessaryData();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($globalItemOptions->all());
    }

    /**
     * Store a new global item
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'name' => 'required|string',
            'site_id' => 'required|string|exists:sites,id',
            'variable_name' => 'required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'description' => 'sometimes|nullable|string',
            'component_id' => 'sometimes|nullable|string|exists:components,id',
            'is_active' => 'sometimes|is_boolean',
            'is_visible' => 'sometimes|is_boolean',
            'categories' => 'sometimes|nullable|array'
        ]);

        $this->authorizeForUser($this->auth->user(), 'create', GlobalItem::class);

        $params = $request->only(['name']);

        if ($request->exists('variable_name')) {
            $params['variable_name'] = $request->input('variable_name');
        }

        if ($request->exists('description')) {
            $params['description'] = $request->input('description');
        }
        
        if ($request->exists('is_active')) {
            $params['is_active'] = $request->input('is_active');
        }

        if ($request->exists('is_visible')) {
            $params['is_visible'] = $request->input('is_visible');
        }

        if ($request->exists('component_id')) {
            if ( ! is_null($request->input('component_id'))) {
                $params['component_id'] = $request->input('component_id');
            }
        }

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $params, $request) {
            /** @var Site $site */
            $site = Site::findOrFail($request->input('site_id'));

            /** @var GlobalItem $globalItem */
            $globalItem = $site->globalItems()->create($params);

            if ($request->exists('categories')) {
                $globalItem->upsertOptionCategoryNames($request->input('categories', []));
            }

            $created = $globalItem->withNecessaryData()->all();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple global items
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $rules = [
            'data' => 'required|array',
            'data.*.name' => 'sometimes|required|string',
            'data.*.site_id' => 'sometimes|required|string|exists:sites,id',
            'data.*.variable_name' => 'required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'data.*.description' => 'sometimes|nullable|string',
            'data.*.is_active' => 'sometimes|is_boolean',
            'data.*.is_visible' => 'sometimes|is_boolean',
            'data.*.categories' => 'sometimes|nullable|array'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:global_items,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedGlobalItems = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var GlobalItem $item */
                $item = GlobalItem::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $item);

                $this->guardAgainstInvalidateRequest($value, [
                    'variable_name' => [
                        'sometimes',
                        'required',
                        'string',
                        'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                        Rule::unique('global_items', 'variable_name')
                            ->where('site_id', $item->site_id)
                            ->ignore($item->{$this->idName}, $this->idName)
                    ]
                ]);

                if (array_key_exists('site_id', $value)) {
                    /** @var Site $site */
                    $site = Site::findOrFail($value['site_id']);
                    $item->site()->associate($site);
                }

                if (array_key_exists('categories', $value)) {
                    $item->upsertOptionCategoryNames($value['categories'] ?: []);
                }

                $params = collect($value)->except([
                    $this->idName,
                    'component_id',
                    'display_order'
                ])->toArray();

                $item->update($params);

                array_push($ids, $item->getKey());
            }
        });

        if ( ! empty($ids)) {
            /** @var GlobalItem[]|\Illuminate\Support\Collection $items */
            $items = GlobalItem::whereIn($this->idName, $ids)->orderBy('display_order')->get();

            foreach ($items as $item) {
                array_push($updatedGlobalItems, $item->withNecessaryData()->all());
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedGlobalItems);
    }

    /**
     * Update a global item by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id)
    {
        /** @var GlobalItem $item */
        $item = GlobalItem::findOrFail($id);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.name' => 'sometimes|required|string',
            'data.site_id' => 'sometimes|required|string|exists:sites,id',
            'data.variable_name' => [
                'sometimes',
                'required',
                'string',
                'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                Rule::unique('global_items', 'variable_name')
                    ->where('site_id', $item->site_id)
                    ->ignore($item->{$this->idName}, $this->idName)
            ],
            'data.description' => 'sometimes|nullable|string',
            'data.is_active' => 'sometimes|is_boolean',
            'data.is_visible' => 'sometimes|is_boolean',
            'data.categories' => 'sometimes|nullable|array'
        ]);

        $this->authorizeForUser($this->auth->user(), 'update', $item);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($item, $data) {
            if (array_key_exists('site_id', $data)) {
                /** @var Site $site */
                $site = Site::findOrFail($data['site_id']);
                $item->site()->associate($site);
            }

            if (array_key_exists('categories', $data)) {
                $item->upsertOptionCategoryNames($data['categories'] ?: []);
            }

            $params = collect($data)->except([
                $this->idName,
                'component_id',
                'display_order'
            ])->toArray();

            $item->update($params);
        });

        $updated = $item->withNecessaryData()->all();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Delete multiple global items
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:global_items,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var GlobalItem $item */
                $item = GlobalItem::findOrFail($value[$this->idName]);
                $this->authorizeForUser($this->auth->user(), 'delete', $item);
                $item->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a global item by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var GlobalItem $item */
        $item = GlobalItem::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $item);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($item) {
            $item->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }
}
