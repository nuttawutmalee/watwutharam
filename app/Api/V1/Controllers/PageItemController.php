<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\Page;
use App\Api\Models\PageItem;
use App\Api\Models\PageItemOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PageItemController extends BaseController
{
    /**
     * PageItemController constructor.
     */
    function __construct()
    {
        $this->idName = (new PageItem)->getKeyName();
    }

    /**
     * Return all page items
     *
     * @param Request $request
     * @return mixed
     */
    public function getPageItems(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @var PageItem[]|\Illuminate\Support\Collection $pageItems */
        $pageItems = PageItem::orderBy('display_order')->get();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($pageItems);
    }

    /**
     * Return a page item by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getPageItemById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var PageItem $pageItem */
        $pageItem = PageItem::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($pageItem);
    }

    /**
     * Return page item options by its parent page item id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getPageItemOptionsByPageItemId(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var PageItem $pageItem */
        $pageItem = PageItem::findOrFail($id);

        /** @var PageItemOption[]|\Illuminate\Support\Collection $pageItemOptions */
        $pageItemOptions = $pageItem->pageItemOptions;

        $pageItemOptions->transform(function ($item) {
            /** @var PageItemOption $item */
            return $item->withNecessaryData();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($pageItemOptions->all());
    }

    /**
     * Store a new page item
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'name' => 'required|string',
            'page_id' => 'required|string|exists:pages,id',
            'variable_name' => 'required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'description' => 'sometimes|nullable|string',
            'component_id' => 'sometimes|nullable|string|exists:components,id',
            'global_item_id' => 'sometimes|nullable|string|exists:global_items,id',
            'is_required' => 'sometimes|is_boolean',
            'is_active' => 'sometimes|is_boolean',
            'is_visible' => 'sometimes|is_boolean'
        ]);

        $this->authorizeForUser($this->auth->user(), 'create', PageItem::class);

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

        if ($request->exists('is_required')) {
            $params['is_required'] = $request->input('is_required');
        }

        if ($request->exists('is_visible')) {
            $params['is_visible'] = $request->input('is_visible');
        }

        if ($request->exists('component_id')) {
            if ( ! is_null($request->input('component_id'))) {
                $params['component_id'] = $request->input('component_id');
            }
        }

        if ($request->exists('global_item_id')) {
            if ( ! is_null($request->input('global_item_id'))) {
                $params['global_item_id'] = $request->input('global_item_id');
            }
        }

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $params, $request) {
            /** @var Page $page */
            $page = Page::findOrFail($request->input('page_id'));

            /** @var PageItem $created */
            $created = $page->pageItems()->create($params);
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple page items
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $rules = [
            'data' => 'required|array',
            'data.*.name' => 'sometimes|required|string',
            'data.*.page_id' => 'sometimes|required|string|exists:pages,id',
            'data.*.variable_name' => 'sometimes|required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'data.*.description' => 'sometimes|nullable|string',
            'data.*.global_item_id' => 'sometimes|nullable|string|exists:global_items,id',
            'data.*.is_required' => 'sometimes|is_boolean',
            'data.*.is_active' => 'sometimes|is_boolean',
            'data.*.is_visible' => 'sometimes|is_boolean'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:page_items,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedPageItems = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var PageItem $item */
                $item = PageItem::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $item);

                $this->guardAgainstInvalidateRequest($value, [
                    'variable_name' => [
                        'sometimes',
                        'required',
                        'string',
                        'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                        Rule::unique('page_items', 'variable_name')
                            ->where('page_id', $item->page_id)
                            ->ignore($item->{$this->idName}, $this->idName)
                    ]
                ]);

                if (array_key_exists('page_id', $value)) {
                    /** @var Page $page */
                    $page = Page::findOrFail($value['page_id']);
                    $item->page()->associate($page);
                }

                $params = collect($value)->except([
                    $this->idName,
                    'display_order',
                    'component_id'
                ])->toArray();

                $item->update($params);

                array_push($ids, $item->getKey());
            }
        });

        if ( ! empty($ids)) {
            /** @var PageItem[]|\Illuminate\Support\Collection $updatedPageItems */
            $updatedPageItems = PageItem::whereIn($this->idName, $ids)->orderBy('display_order')->get();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedPageItems);
    }

    /**
     * Update a page item by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id)
    {
        /** @var PageItem $item */
        $item = PageItem::findOrFail($id);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.name' => 'sometimes|required|string',
            'data.page_id' => 'sometimes|required|string|exists:pages,id',
            'data.variable_name' => [
                'sometimes',
                'required',
                'string',
                'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                Rule::unique('page_items', 'variable_name')
                    ->where('page_id', $item->page_id)
                    ->ignore($item->{$this->idName}, $this->idName)
            ],
            'data.description' => 'sometimes|nullable|string',
            'data.global_item_id' => 'sometimes|nullable|string|exists:global_items,id',
            'data.is_required' => 'sometimes|is_boolean',
            'data.is_active' => 'sometimes|is_boolean',
            'data.is_visible' => 'sometimes|is_boolean'
        ]);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($item, $data) {
            if (array_key_exists('page_id', $data)) {
                /** @var Page $page */
                $page = Page::findOrFail($data['page_id']);
                $item->page()->associate($page);
            }

            $params = collect($data)->except([
                $this->idName,
                'display_order',
                'component_id'
            ])->toArray();

            $item->update($params);
        });

        $updated = $item->fresh();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Delete multiple page items
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:page_items,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var PageItem $item */
                $item = PageItem::findOrFail($value[$this->idName]);
                $this->authorizeForUser($this->auth->user(), 'delete', $item);
                $item->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a page item by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var PageItem $item */
        $item = PageItem::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $item);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($item) {
            $item->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }
}
