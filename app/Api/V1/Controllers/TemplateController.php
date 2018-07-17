<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\Site;
use App\Api\Models\Template;
use App\Api\Models\TemplateItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TemplateController extends BaseController
{
    /**
     * TemplateController constructor.
     */
    function __construct()
    {
        $this->idName = (new Template)->getKeyName();
    }

    /**
     * Return all templates
     *
     * @param Request $request
     * @return mixed
     */
    public function getTemplates(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(Template::all());
    }

    /**
     * Return a template by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getTemplateById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Template $template */
        $template = Template::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($template);
    }

    /**
     * Return all pages by its template id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getPagesByTemplateId(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Template $template */
        $template = Template::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($template->pages);
    }

    /**
     * Return all template items by its template id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getTemplateItemsByTemplateId(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Template $template */
        $template = Template::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($template->templateItems()->orderBy('display_order')->get());
    }

    /**
     * Store a new template
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'name' => 'required|string',
            'variable_name' => 'required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'site_id' => 'required|string|exists:sites,id',
            'description' => 'sometimes|nullable|string'
        ]);

        $this->authorizeForUser($this->auth->user(), 'create', Template::class);

        $params = $request->only(['name', 'variable_name']);

        if ($request->exists('description')) {
            $params['description'] = $request->input('description');
        }

        /** @var Site $site */
        $site = Site::findOrFail($request->input('site_id'));

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $site, $params) {
            /** @var Template $template */
            $template = $site->templates()->create($params);
            $created = $template->fresh();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple templates
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
            'data.*.description' => 'sometimes|nullable|string',
            'data.*.variable_name' => 'sometimes|required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:templates,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedTemplates = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var Template $template */
                $template = Template::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $template);

                $this->guardAgainstInvalidateRequest($value, [
                    'variable_name' => [
                        'sometimes',
                        'required',
                        'string',
                        'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                        Rule::unique('templates', 'variable_name')
                            ->where('site_id', $template->site_id)
                            ->ignore($template->{$this->idName}, $this->idName)
                    ]
                ]);

                if (array_key_exists('site_id', $value)) {
                    /** @var Site $site */
                    $site = Site::findOrFail($value['site_id']);
                    $template->site()->associate($site);
                }

                $value = collect($value)->except($this->idName)->toArray();

                $template->update($value);

                array_push($ids, $template->getKey());
            }
        });

        if ( ! empty($ids)) {
            /** @var Template[]|\Illuminate\Support\Collection $updatedTemplates */
            $updatedTemplates = Template::whereIn($this->idName, $ids)->get();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedTemplates);
    }

    /**
     * Update a template by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id)
    {
        /** @var Template $template */
        $template = Template::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $template);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.name' => 'sometimes|required|string',
            'data.variable_name' => [
                'sometimes',
                'required',
                'string',
                'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                Rule::unique('templates', 'variable_name')
                    ->where('site_id', $template->site_id)
                    ->ignore($template->{$this->idName}, $this->idName)
            ],
            'data.site_id' => 'sometimes|required|string|exists:sites,id',
            'data.description' => 'sometimes|nullable|string'
        ]);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($template, $data) {
            if (array_key_exists('site_id', $data)) {
                /** @var Site $site */
                $site = Site::findOrFail($data['site_id']);
                $template->site()->associate($site);
            }

            $data = collect($data)->except($this->idName)->toArray();

            $template->update($data);
        });

        /** @var Template $updated */
        $updated = $template->fresh();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Delete multiple templates
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array',
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:templates,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var Template $template */
                $template = Template::findOrFail($value[$this->idName]);
                $this->authorizeForUser($this->auth->user(), 'delete', $template);
                $template->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a template by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Template $template */
        $template = Template::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $template);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($template) {
            $template->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Reorder template items by its template id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function reorderTemplateItemsByTemplateId(Request $request, $id)
    {
        /** @var Template $template */
        $template = Template::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $template);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.*.id' => 'required|distinct|string|exists:template_items,id',
            'data.*.display_order' => 'required|integer|distinct|min:1'
        ]);

        $data = $request->input('data');
        $dataCollection = collect($data);

        $ids = $dataCollection->pluck('id')->all() ?: [];

        /** @var TemplateItem[]|\Illuminate\Support\Collection $items */
        $items = $template->templateItems()->whereIn('id', $ids)->get();

        if ( ! empty($items)) {
            /** @noinspection PhpUndefinedMethodInspection */
            DB::transaction(function () use ($dataCollection, $items) {
                /** @var TemplateItem[]|\Illuminate\Support\Collection $items */
                $items->each(function ($item) use ($dataCollection) {
                    /**
                     * @var TemplateItem $item
                     * @var TemplateItem $filtered
                     */
                    if ($filtered = $dataCollection->where('id', $item->getKey())->first()) {
                        if ($displayOrder = collect($filtered)->get('display_order')) {
                            $item->display_order = $displayOrder;
                            $item->save();
                        }
                    }
                });
            });
        }

        /** @var TemplateItem[]|\Illuminate\Support\Collection $updated */
        $updated = $template->templateItems()->whereIn('id', $ids)->orderBy('display_order')->get();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }
}
