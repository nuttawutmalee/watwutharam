<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\LogConstants;
use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\CmsLog;
use App\Api\Models\Component;
use App\Api\Models\ComponentOption;
use App\Api\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ComponentController extends BaseController
{
    /**
     * ComponentController constructor.
     */
    function __construct()
    {
        $this->idName = (new Component)->getKeyName();
    }

    /**
     * Return all components
     *
     * @param Request $request
     * @return mixed
     */
    public function getComponents(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(Component::all());
    }

    /**
     * Return a component by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getComponentById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Component $component */
        $component = Component::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($component);
    }

    /**
     * Return component options by its parent component id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getComponentOptionsByComponentId(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Component $component */
        $component = Component::findOrFail($id);

        /** @var ComponentOption[]|\Illuminate\Support\Collection $componentOptions */
        $componentOptions = $component->componentOptions;

        $componentOptions->transform(function ($item) {
            /** @var ComponentOption $item */
            return $item->withNecessaryData();
        });
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($componentOptions->all());
    }

    /**
     * Return inherit template items
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getTemplateItems(/** @noinspection PhpUnusedParameterInspection */Request $request, $id)
    {
        /** @var Component $component */
        $component = Component::findOrFail($id);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($component->templateItems);
    }

    /**
     * Return inherit page items
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getPageItems(/** @noinspection PhpUnusedParameterInspection */Request $request, $id)
    {
        /** @var Component $component */
        $component = Component::findOrFail($id);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($component->pageItems);
    }

    /**
     * Return inherit global items
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getGlobalItems(/** @noinspection PhpUnusedParameterInspection */Request $request, $id)
    {
        /** @var Component $component */
        $component = Component::findOrFail($id);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($component->globalItems);
    }

    /**
     * Return all inheritances by component id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getInheritances(/** @noinspection PhpUnusedParameterInspection */Request $request, $id)
    {
        /** @var Component $component */
        $component = Component::findOrFail($id);

        $inheritances = [];

        if ($component->count_inheritances > 0) {
            $templateItems = $component->templateItems;
            $pageItems = $component->pageItems;
            $globalItems = $component->globalItems;

            $inheritances = array_merge($templateItems->toArray(), $pageItems->toArray(), $globalItems->toArray());
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($inheritances);
    }

    /**
     * Return all inheritances by component id and site id
     *
     * @param Request $request
     * @param $id
     * @param $siteId
     * @return mixed
     */
    public function getInheritancesBySiteId(/** @noinspection PhpUnusedParameterInspection */Request $request, $id, $siteId)
    {
        /** @var Component $component */
        $component = Component::findOrFail($id);

        /** @var Site $site */
        $site = Site::findOrFail($siteId);

        $inheritances = [];

        if ($component->count_inheritances > 0) {
            $templateItems = $component
                ->templateItems()
                ->whereHas('template.site', function ($query) use ($site) {
                    /** @var \Illuminate\Database\Eloquent\Builder $query */
                    $query->where('id', $site->getKey());
                })
                ->get();

            $pageItems = $component
                ->pageItems()
                ->whereHas('page.template.site', function ($query) use ($site) {
                    /** @var \Illuminate\Database\Eloquent\Builder $query */
                    $query->where('id', $site->getKey());
                })
                ->get();

            $globalItems = $component
                ->globalItems()
                ->where('site_id', $site->getKey())
                ->get();

            $inheritances = array_merge($templateItems->toArray(), $pageItems->toArray(), $globalItems->toArray());
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($inheritances);
    }

    /**
     * Store a new component
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'name' => 'required|string',
            'variable_name' => 'required|string|unique:components|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'description' => 'sometimes|nullable|string'
        ]);

        $this->authorizeForUser($this->auth->user(), 'create', Component::class);

        $params = $request->only(['name', 'variable_name']);

        if ($request->exists('description')) {
            $params['description'] = $request->input('description');
        }

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $params) {
            /** @var Component $component */
            $component = Component::create($params);
            $created = $component->fresh();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple components
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $rules = [
            'data' => 'required|array',
            'data.*.name' => 'sometimes|required|string',
            'data.*.description' => 'sometimes|nullable|string'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:components,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedComponents = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var Component $component */
                $component = Component::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $component);
                
                $this->guardAgainstInvalidateRequest($value, [
                    'variable_name' => [
                        'sometimes',
                        'required',
                        'string',
                        'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                        Rule::unique('components', 'variable_name')->ignore($component->{$this->idName}, $this->idName)
                    ]
                ]);

                $value = collect($value)->except($this->idName)->toArray();
               
                $component->update($value);

                array_push($ids, $component->getKey());
            }
        });

        if ( ! empty($ids)) {
            /** @var Component[]|\Illuminate\Support\Collection $updatedComponents */
            $updatedComponents = Component::whereIn($this->idName, $ids)->get();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedComponents);
    }

    /**
     * Update a component by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id)
    {
        /** @var Component $component */
        $component = Component::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $component);
        
        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.name' => 'sometimes|required|string',
            'data.variable_name' => [
                'sometimes',
                'required',
                'string',
                'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                Rule::unique('components', 'variable_name')->ignore($component->{$this->idName}, $this->idName)
            ],
            'data.description' => 'sometimes|nullable|string'
        ]);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($component, $data) {
            $data = collect($data)->except($this->idName)->toArray();
            $component->update($data);
        });

        $updated = $component->fresh();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Update inheritances (template/page/global) items of component by id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateInheritances(Request $request, $id)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'template_item_ids' => 'sometimes|nullable|array',
            'page_item_ids' => 'sometimes|nullable|array',
            'global_item_ids' => 'sometimes|nullable|array'
        ]);

        /** @var Component $component */
        $component = Component::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $component);

        $templateItemIds = $request->input('template_item_ids', []);
        $pageItemIds = $request->input('page_item_ids', []);
        $globalItemIds = $request->input('global_item_ids', []);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($component, $templateItemIds, $pageItemIds, $globalItemIds, &$updatedCount) {
            if ( ! empty($templateItemIds)) {
                $updatedTemplateItems = $component
                    ->templateItems()
                    ->whereIn('id', $templateItemIds)
                    ->get();

                foreach ($updatedTemplateItems as $updatedTemplateItem) {
                    $this->authorizeForUser($this->auth->user(), 'update', $updatedTemplateItem);
                    $updatedTemplateItem->updateInheritComponentOptions();
                }
            }

            if ( ! empty($pageItemIds)) {
                $updatedPageItems = $component
                    ->pageItems()
                    ->whereIn('id', $pageItemIds)
                    ->get();

                foreach ($updatedPageItems as $updatedPageItem) {
                    $this->authorizeForUser($this->auth->user(), 'update', $updatedPageItem);
                    $updatedPageItem->updateInheritComponentOptions();
                }
            }

            if ( ! empty($globalItemIds)) {
                $updatedGlobalItems = $component
                    ->globalItems()
                    ->whereIn('id', $globalItemIds)
                    ->get();

                foreach ($updatedGlobalItems as $updatedGlobalItem) {
                    $this->authorizeForUser($this->auth->user(), 'update', $updatedGlobalItem);
                    $updatedGlobalItem->updateInheritComponentOptions();
                }
            }
        });

        CmsLog::log($component, LogConstants::COMPONENT_CASCADE_UPDATE_INHERITANCES);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(true);
    }

    /**
     * Delete multiple components
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array',
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:components,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var Component $component */
                $component = Component::findOrFail($value[$this->idName]);
                $this->authorizeForUser($this->auth->user(), 'delete', $component);
                $component->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a component by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Component $component */
        $component = Component::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $component);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($component) {
            $component->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }
}
