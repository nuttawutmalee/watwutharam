<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\Page;
use App\Api\Models\PageItem;
use App\Api\Models\Template;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class PageController extends BaseController
{
    /**
     * PageController constructor.
     */
    function __construct()
    {
        $this->idName = (new Page)->getKeyName();
    }

    /**
     * Return all pages
     *
     * @param Request $request
     * @return mixed
     */
    public function getPages(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @var Page[]|\Illuminate\Support\Collection $pages */
        $pages = Page::all();

        $pages->transform(function ($page) {
            /** @var Page $page */
            return $page->withNecessaryData(['parents', 'children']);
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($pages->all());
    }

    /**
     * Return a page by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getPageById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Page $page */
        $page = Page::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($page->withNecessaryData(['parents', 'children'])->all());
    }

    /**
     * Return page items by its parent page id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getPageItemsByPageId(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Page $page */
        $page = Page::findOrFail($id);

        /** @var PageItem[]|\Illuminate\Support\Collection $pageItems */
        $pageItems = $page
            ->pageItems()
            ->orderBy('display_order')
            ->get();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($pageItems);
    }

    /**
     * Store a new page
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'variable_name' => 'required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'friendly_url' => 'required|string',
            'template_id' => 'sometimes|required|string|exists:templates,id',
            'is_active' => 'sometimes|is_boolean',
            'description' => 'sometimes|nullable|string',
            'parent_ids' => 'sometimes|nullable|array',
            'categories' => 'sometimes|nullable|array',
            'permissions' => 'sometimes|nullable|string',
            'published_at' => 'sometimes|nullable|string'
        ];

        $this->authorizeForUser($this->auth->user(), 'create', Page::class);

        $parentIds = $request->input('parent_ids', []);

        if ( ! empty($parentIds)) {
            foreach ($parentIds as $key => $value) {
                $rules['parent_ids.' . $key] = 'string|exists:pages,' . $this->idName;
            }
        }

        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $params = $request->only(['name', 'variable_name', 'friendly_url']);

        $params['friendly_url'] = $this->validateFriendlyUrl($params['friendly_url']);

        if ($request->exists('is_active')) {
            $params['is_active'] = $request->input('is_active');
        }

        if ($request->exists('description')) {
            $params['description'] = $request->input('description');
        }

        if ($request->exists('permissions')) {
            $params['permissions'] = $request->input('permissions');
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if ($request->exists('published_at') && Schema::hasColumn('pages', 'published_at')) {
            $publishedAt = $request->input('published_at');

            if ( ! is_null($publishedAt)) {
                // Change published at to UTC/GMT
                $date = Carbon::parse($publishedAt);
                $params['published_at'] = Carbon::createFromTimestampUTC($date->timestamp);
            }
        }

        /** @var Template $template */
        $template = Template::findOrFail($request->input('template_id'));

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $template, $params, $parentIds, $request) {
            /** @var Page $page */
            $page = $template->pages()->create($params);

            if ( ! empty($parentIds)) {
                foreach ($parentIds as $id) {
                    /** @var Page $parent */
                    $parent = Page::findOrFail($id);
                    $this->guardAgainstInvalidParentPage($page, $parent);
                }
            }

            if ($request->exists('categories')) {
                $page->upsertOptionCategoryNames($request->input('categories', []));
            }

            $page->parents()->sync($parentIds);

            $created = $page->withNecessaryData(['parents', 'children'])->all();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple pages
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
            'data.*.is_active' => 'sometimes|is_boolean',
            'data.*.description' => 'sometimes|nullable|string',
            'data.*.parent_ids' => 'sometimes|nullable|array',
            'data.*.variable_name' => 'sometimes|required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'data.*.friendly_url' => 'sometimes|required|string',
            'data.*.categories' => 'sometimes|nullable|array',
            'data.*.permissions' => 'sometimes|nullable|string',
            'data.*.published_at' => 'sometimes|nullable|string'
        ];

        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:pages,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedPages = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var Page $page */
                $page = Page::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $page);

                $this->guardAgainstInvalidateRequest($value, [
                    'variable_name' => [
                        'sometimes',
                        'required',
                        'string',
                        'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                        Rule::unique('pages', 'variable_name')
                            ->where('template_id', $page->template_id)
                            ->ignore($page->{$this->idName}, $this->idName)
                    ],
                    'friendly_url' => [
                        'sometimes',
                        'required',
                        'string',
                        Rule::unique('pages', 'friendly_url')
                            ->where('template_id', $page->template_id)
                            ->ignore($page->{$this->idName}, $this->idName)
                    ]
                ]);

                $rules = [];
                $parentIds = [];
                $parentIdsExists = false;
                if (array_key_exists('parent_ids', $value)) {
                    $parentIdsExists = true;
                    if ( ! empty($value['parent_ids'])) {
                        $parentIds = $value['parent_ids'];
                        foreach ($parentIds as $k => $v) {
                            $rules['parent_ids.' . $k] = 'string|exists:pages,' . $this->idName;
                        }
                    }
                }

                $this->guardAgainstInvalidateRequest($value, $rules);

                if (array_key_exists('template_id', $value)) {
                    /** @var Template $template */
                    $template = Template::findOrFail($value['template_id']);
                    $page->template()->associate($template);
                }

                if ( ! empty($parentIds)) {
                    foreach ($parentIds as $k => $v) {
                        /** @var Page $parent */
                        $parent = Page::findOrFail($v);
                        $this->guardAgainstInvalidParentPage($page, $parent);
                    }
                }

                if (array_key_exists('friendly_url', $value)) {
                    $value['friendly_url'] = $this->validateFriendlyUrl($value['friendly_url']);
                }

                /** @noinspection PhpUndefinedMethodInspection */
                if (array_key_exists('published_at', $value) && ! is_null($value['published_at']) && Schema::hasColumn('pages', 'published_at')) {
                    // Change published at to UTC/GMT
                    $date = Carbon::parse($value['published_at']);
                    $value['published_at'] = Carbon::createFromTimestampUTC($date->timestamp);
                }

                if (array_key_exists('categories', $value)) {
                    $page->upsertOptionCategoryNames($value['categories'] ?: []);
                }

                if ($parentIdsExists) {
                    $page->parents()->sync($parentIds);
                }

                /** @noinspection PhpUndefinedMethodInspection */
                $excepts = Schema::hasColumn('pages', 'published_at') ? $this->idName : [$this->idName, 'published_at'];

                $value = collect($value)->except($excepts)->toArray();

                $page->update($value);

                array_push($ids, $page->getKey());
            }
        });

        if ( ! empty($ids)) {
            /** @var Page[]|\Illuminate\Support\Collection $pages */
            $pages = Page::whereIn($this->idName, $ids)->get();

            foreach ($pages as $page) {
                array_push($updatedPages, $page->withNecessaryData(['parents', 'children'])->all());
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedPages);
    }

    /**
     * Update a page by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id)
    {
        /** @var Page $page */
        $page = Page::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $page);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.name' => 'sometimes|required|string',
            'data.template_id' => 'sometimes|required|string|exists:templates,id',
            'data.is_active' => 'sometimes|is_boolean',
            'data.description' => 'sometimes|nullable|string',
            'data.variable_name' => [
                'sometimes',
                'required',
                'string',
                'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                Rule::unique('pages', 'variable_name')
                    ->where('template_id', $page->template_id)
                    ->ignore($page->{$this->idName}, $this->idName)
            ],
            'data.friendly_url' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('pages', 'friendly_url')
                    ->where('template_id', $page->template_id)
                    ->ignore($page->{$this->idName}, $this->idName)
            ],
            'data.parent_ids' => 'sometimes|nullable|array',
            'data.categories' => 'sometimes|nullable|array',
            'data.permissions' => 'sometimes|nullable|string',
            'data.published_at' => 'sometimes|nullable|string'
        ]);

        $data = $request->input('data');

        $parentRules = [];
        $parentIds = [];
        $parentIdsExists = false;
        if (array_key_exists('parent_ids', $data)) {
            $parentIdsExists = true;
            if ( ! empty($data['parent_ids'])) {
                $parentIds = $data['parent_ids'];
                foreach ($parentIds as $key => $value) {
                    $parentRules['parent_ids.' . $key] = 'string|exists:pages,' . $this->idName;
                }
            }
        }

        $this->guardAgainstInvalidateRequest($data, $parentRules);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($page, $data, $parentIds, $parentIdsExists) {
            if (array_key_exists('template_id', $data)) {
                /** @var Template $template */
                $template = Template::findOrFail($data['template_id']);
                $page->template()->associate($template);
            }

            if ( ! empty($parentIds)) {
                foreach ($parentIds as $id) {
                    /** @var Page $parent */
                    $parent = Page::findOrFail($id);
                    $this->guardAgainstInvalidParentPage($page, $parent);
                }
            }

            if (array_key_exists('friendly_url', $data)) {
                $data['friendly_url'] = $this->validateFriendlyUrl($data['friendly_url']);
            }

            /** @noinspection PhpUndefinedMethodInspection */
            if (array_key_exists('published_at', $data) && ! is_null($data['published_at']) && Schema::hasColumn('pages', 'published_at')) {
                // Change published at to UTC/GMT
                $date = Carbon::parse($data['published_at']);
                $data['published_at'] = Carbon::createFromTimestampUTC($date->timestamp);
            }

            if (array_key_exists('categories', $data)) {
                $page->upsertOptionCategoryNames($data['categories'] ?: []);
            }

            if ($parentIdsExists) {
                $page->parents()->sync($parentIds);
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $excepts = Schema::hasColumn('pages', 'published_at') ? $this->idName : [$this->idName, 'published_at'];

            $data = collect($data)->except($excepts)->toArray();

            $page->update($data);
        });

        $updated = $page->withNecessaryData(['parents', 'children'])->all();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Delete multiple pages
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:pages,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var Page $page */
                $page = Page::findOrFail($value[$this->idName]);
                $this->authorizeForUser($this->auth->user(), 'delete', $page);
                $page->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a page by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Page $page */
        $page = Page::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $page);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($page) {
            $page->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Reorder page items by its parent page id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function reorderPageItemsByPageId(Request $request, $id)
    {
        /** @var Page $page */
        $page = Page::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $page);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.*.id' => 'required|distinct|string|exists:page_items,id',
            'data.*.display_order' => 'required|integer|distinct|min:1'
        ]);

        $data = $request->input('data');
        $dataCollection = collect($data);

        $ids = $dataCollection->pluck('id')->all() ?: [];

        /** @var PageItem[]|\Illuminate\Support\Collection $items */
        $items = $page->pageItems()->whereIn('id', $ids)->get();

        if ( ! empty($items)) {
            /** @noinspection PhpUndefinedMethodInspection */
            DB::transaction(function () use ($dataCollection, $items) {
                /** @var PageItem[]|\Illuminate\Support\Collection $items */
                $items->each(function ($item) use ($dataCollection) {
                    /**
                     * @var PageItem $item
                     * @var PageItem $filtered
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

        /** @var PageItem[]|\Illuminate\Support\Collection $updated */
        $updated = $page->pageItems()->whereIn('id', $ids)->orderBy('display_order')->get();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Throw an exception if the parent page is invalid
     *
     * @param Page $page
     * @param Page $parent
     * @throws \Exception
     */
    private function guardAgainstInvalidParentPage(Page $page, Page $parent)
    {
        if ( ! $page instanceof Page ||
             ! $parent instanceof Page) {
            throw new \Exception(ErrorMessageConstants::WRONG_MODEL);
        }

        if ($page->is($parent)) {
            throw new \Exception(ErrorMessageConstants::CANNOT_ATTACH_TO_ITSELF);
        }

        try {
            $site = $page->template->site;
            $parentSite = $parent->template->site;

            if ( ! $site->is($parentSite)) {
                throw new \Exception(ErrorMessageConstants::FROM_DIFFERENT_SITE);
            }

            if ($loop = $parent->parents()->where('id', $page->{$this->idName})->first()) {
                throw new \Exception(ErrorMessageConstants::LOOP_RELATIONSHIP_DETECTED);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Throw an exception if the friendly url is invalid
     *
     * @param $friendlyUrl
     * @return mixed
     * @throws \Exception
     */
    private function validateFriendlyUrl($friendlyUrl)
    {
        $friendlyUrl = preg_replace('/\/|\\\/', '/', $friendlyUrl);
        $friendlyUrl = preg_replace('/^\//', '', $friendlyUrl);

        if (preg_match('/[^\w\/-]/', $friendlyUrl)) {
            throw new \Exception(ErrorMessageConstants::INVALID_FRIENDLY_URL);
        }

        $reserved = ValidationRuleConstants::RESERVED_FRIENDLY_URL;

        if ( ! empty($reserved)) {
            $reserved = is_array($reserved) ? join('|', $reserved) : $reserved;
            if (preg_match('/^(' . preg_quote($reserved, '/') . ')/i', $friendlyUrl)) {
                throw new \Exception(ErrorMessageConstants::INVALID_FRIENDLY_URL);
            }
        }

        return $friendlyUrl;
    }
}
