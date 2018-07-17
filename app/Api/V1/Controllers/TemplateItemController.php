<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\Template;
use App\Api\Models\TemplateItem;
use App\Api\Models\TemplateItemOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TemplateItemController extends BaseController
{
    /**
     * TemplateItemController constructor.
     */
    function __construct()
    {
        $this->idName = (new TemplateItem)->getKeyName();
    }

    /**
     * Return all template items
     *
     * @param Request $request
     * @return mixed
     */
    public function getTemplateItems(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(TemplateItem::orderBy('display_order')->get());
    }

    /**
     * Return a template item by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getTemplateItemById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var TemplateItem $item */
        $item = TemplateItem::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($item);
    }

    /**
     * Return all template item options by its template item id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getTemplateItemOptionsByTemplateItemId(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var TemplateItem $templateItem */
        $templateItem = TemplateItem::findOrFail($id);

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $templateItemOptions */
        $templateItemOptions = $templateItem->templateItemOptions;

        $templateItemOptions->transform(function ($item) {
            /** @var TemplateItemOption $item */
            return $item->withNecessaryData();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($templateItemOptions->all());
    }

    /**
     * Store a new template item
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'name' => 'required|string',
            'template_id' => 'required|string|exists:templates,id',
            'variable_name' => 'required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'description' => 'sometimes|nullable|string',
            'component_id' => 'sometimes|nullable|string|exists:components,id'
        ]);

        $this->authorizeForUser($this->auth->user(), 'create', TemplateItem::class);

        $params = $request->only(['name']);

        if ($request->exists('variable_name')) {
            $params['variable_name'] = $request->input('variable_name');
        }

        if ($request->exists('description')) {
            $params['description'] = $request->input('description');
        }

        if ($request->exists('component_id')) {
            if ( ! is_null($request->input('component_id'))) {
                $params['component_id'] = $request->input('component_id');
            }
        }

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $params, $request) {
            /** @var Template $template */
            $template = Template::findOrFail($request->input('template_id'));

            /** @var TemplateItem $templateItem */
            $templateItem = $template->templateItems()->create($params);

            /** @var TemplateItem $created */
            $created = $templateItem->fresh();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple template items
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $rules = [
            'data' => 'required|array',
            'data.*.name' => 'sometimes|required|string',
            'data.*.template_id' => 'sometimes|required|string|exists:templates,id',
            'data.*.variable_name' => 'sometimes|required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'data.*.description' => 'sometimes|nullable|string',
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:template_items,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedTemplateItems = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var TemplateItem $item */
                $item = TemplateItem::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $item);

                $this->guardAgainstInvalidateRequest($value, [
                    'variable_name' => [
                        'sometimes',
                        'required',
                        'string',
                        'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                        Rule::unique('template_items', 'variable_name')
                            ->where('template_id', $item->template_id)
                            ->ignore($item->{$this->idName}, $this->idName)
                    ]
                ]);

                if (array_key_exists('template_id', $value)) {
                    /** @var Template $template */
                    $template = Template::findOrFail($value['template_id']);
                    $item->template()->associate($template);
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
            /** @var TemplateItem[]|\Illuminate\Support\Collection $updatedTemplateItems */
            $updatedTemplateItems = TemplateItem::whereIn($this->idName, $ids)->orderBy('display_order')->get();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedTemplateItems);
    }

    /**
     * Update a template item by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id)
    {
        /** @var TemplateItem $item */
        $item = TemplateItem::findOrFail($id);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.name' => 'sometimes|required|string',
            'data.template_id' => 'sometimes|required|string|exists:templates,id',
            'data.variable_name' => [
                'sometimes',
                'required',
                'string',
                'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                Rule::unique('template_items', 'variable_name')
                    ->where('template_id', $item->template_id)
                    ->ignore($item->{$this->idName}, $this->idName)
            ],
            'data.description' => 'sometimes|nullable|string',
        ]);

        $this->authorizeForUser($this->auth->user(), 'update', $item);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($item, $data) {
            if (array_key_exists('template_id', $data)) {
                /** @var Template $template */
                $template = Template::findOrFail($data['template_id']);
                $item->template()->associate($template);
            }

            $params = collect($data)->except([
                $this->idName,
                'display_order',
                'component_id'
            ])->toArray();

            $item->update($params);
        });

        /** @var TemplateItem $updated */
        $updated = $item->fresh();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Delete multiple template items
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:template_items,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var TemplateItem $item */
                $item = TemplateItem::findOrFail($value[$this->idName]);
                $this->authorizeForUser($this->auth->user(), 'delete', $item);
                $item->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a template item by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var TemplateItem $item */
        $item = TemplateItem::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $item);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($item) {
            $item->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }
}
