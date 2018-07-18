<?php

namespace App\Api\V1\Controllers;
ini_set('max_execution_time', 1800); // 30 minutes

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\LogConstants;
use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\CmsLog;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\Language;
use App\Api\Models\Page;
use App\Api\Models\Site;
use App\Api\Tools\Excel\SiteTranslationExcelFile;
use App\Api\Tools\Excel\SubmissionExcelFile;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SiteController extends BaseController
{
    private $siteTranslationExcelFile;
    private $submissionExcelFile;

    /**
     * SiteController constructor.
     * @param SiteTranslationExcelFile $siteTranslationExcelFile
     * @param SubmissionExcelFile $submissionExcelFile
     */
    function __construct(SiteTranslationExcelFile $siteTranslationExcelFile, SubmissionExcelFile $submissionExcelFile)
    {
        $this->idName = (new Site)->getKeyName();
        $this->siteTranslationExcelFile = $siteTranslationExcelFile;
        $this->submissionExcelFile = $submissionExcelFile;
    }

    /**
     * Return all sites
     *
     * @param Request $request
     * @return mixed
     */
    public function getSites(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(Site::all());
    }

    /**
     * Return a site by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getSiteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($site);
    }

    /**
     * Return all redirect urls by its parent site id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getRedirectUrls(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($site->redirectUrls);
    }

    /**
     * Return all templates by its parent site id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getTemplates(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($site->templates);
    }

    /**
     * Return all global items by its parent site id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getGlobalItems(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        /** @var GlobalItem[]|\Illuminate\Support\Collection $globalItems */
        $globalItems = $site->globalItems;

        $globalItems->transform(function ($globalItem) {
            /** @var GlobalItem $globalItem */
            return $globalItem->withNecessaryData();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($globalItems);
    }

    public function getGlobalItemsByComponentId(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id, $componentId)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        /** @var GlobalItem[]|\Illuminate\Support\Collection $globalItems */
        $globalItems = $site
            ->globalItems()
            ->where('component_id', $componentId)
            ->get();

        $globalItems->transform(function ($globalItem) {
            /** @var GlobalItem $globalItem */
            return $globalItem->withNecessaryData();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($globalItems);
    }

    /**
     * Return all pages by its parent site id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getPages(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        /** @var Page[]|\Illuminate\Support\Collection $pages */
        $pages = $site->pages;

        $pages->transform(function ($page) {
            /** @var Page $page */
            return $page->withNecessaryData(['parents', 'children']);
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($pages);
    }

    /**
     * Return all site translations by its parent site id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getSiteTranslations(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        /** @var Language[]|\Illuminate\Support\Collection $siteLanguages */
        $siteLanguages = $site->languages;

        $siteTranslations = collect([]);

        if ( ! empty($siteLanguages)) {
            $siteLanguages->each(function ($item) use (&$siteTranslations) {
                /** @var Language $item */
                $siteTranslations[$item->code] = $item->siteTranslations;
            });
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($siteTranslations);
    }

    /**
     * Return all site translations by its parent site id and language code
     *
     * @param Request $request
     * @param $id
     * @param $code
     * @return mixed
     */
    public function getSiteTranslationsByLanguageCode(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id, $code)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        /** @var Language $siteLanguage */
        $siteLanguage = $site->languages()->where('language_code', strtolower($code))->firstOrFail();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($siteLanguage->siteTranslations);
    }

    /**
     * Return all site languages by its parent site id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getSiteLanguagesBySiteId(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        /** @var Language[]|\Illuminate\Support\Collection $siteLanguages */
        $siteLanguages = $site->languages;

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($siteLanguages);
    }

    /**
     * Return all site languages by its parent site id and language code
     *
     * @param Request $request
     * @param $siteId
     * @param $code
     * @return mixed
     */
    public function getSiteLanguageBySiteIdAndCode(/** @noinspection PhpUnusedParameterInspection */ Request $request, $siteId, $code)
    {
        /** @var Site $site */
        $site = Site::findOrFail($siteId);

        /** @var Language $siteLanguage */
        $siteLanguage = $site->languages()->findOrFail(strtolower($code));

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($siteLanguage);
    }

    /**
     * Store a new site
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'domain_name' => 'required|min:3|unique:sites',
            'site_url' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|is_boolean'
        ]);

        $this->authorizeForUser($this->auth->user(), 'create', Site::class);

        $params = $request->only('domain_name');
        if ($request->exists('description')) {
            $params['description'] = $request->input('description');
        }
        /** @noinspection PhpUndefinedMethodInspection */
        if ($request->exists('site_url') && Schema::hasColumn('sites', 'site_url')) {
            $params['site_url'] = $request->input('site_url');
        }
        if ($request->exists('is_active')) {
            $params['is_active'] = $request->input('is_active');
        }

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $params) {
            /** @var Site $site */
            $site = Site::create($params);
            $site->selectNewMainLanguageIfNoneExists();
            $created = $site->fresh();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Attach a site language to a site by its site id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function attachSiteLanguageBySiteId(Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'language_code' => [
                'required',
                'string',
                'exists:languages,code',
                Rule::unique('site_languages', 'language_code')->where(function ($query) use ($site) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $query->where('site_id', $site->{$this->idName});
                })
            ],
            'is_main' => 'sometimes|is_boolean',
            'is_active' => 'sometimes|is_boolean'
        ]);

        /** @var Language $language */
        $language = Language::findOrFail($request->input('language_code'));

        $this->authorizeForUser($this->auth->user(), 'update', $site);

        $data = $request->except(['site_id', 'language_code', 'display_order']);

        if ($request->exists('is_main')) {
            if (to_boolean($request->input('is_main'))) {
                /** @var Language[]|\Illuminate\Support\Collection $languages */
                $languages = $site->languages;

                /** @var Language $lang */
                foreach ($languages as $lang) {
                    $site->languages()->updateExistingPivot($lang->getKey(), ['is_main' => false]);
                }

                $data['is_active'] = true;
            }
        }

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = to_boolean($data['is_active']);
        }

        if (array_key_exists('is_main', $data)) {
            $data['is_main'] = to_boolean($data['is_main']);
        }

        $site = $site->fresh('languages');

        if ( ! empty($site->languages)) {
            $languages = $site->languages;
            foreach ($languages as $lang) {
                $syncData[$lang->getKey()] = collect($lang->pivot)->only(['is_active', 'is_main', 'display_order'])->toArray();
            }
        }

        $syncData[$language->getKey()] = $data;

        $site->languages()->sync($syncData);

        $site->selectNewMainLanguageIfNoneExists();

        /** @var Language $created */
        $created = $site->fresh('languages')->languages()->findOrFail($language->code);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple sites
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $rules = [
            'data' => 'required|array',
            'data.*.site_url' => 'sometimes|nullable|string',
            'data.*.description' => 'sometimes|nullable|string',
            'data.*.is_active' => 'sometimes|is_boolean'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:sites,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedSites = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var Site $site */
                $site = Site::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $site);

                $this->guardAgainstInvalidateRequest($value, [
                    'domain_name' => [
                        'sometimes',
                        'required',
                        'string',
                        'min:3',
                        Rule::unique('sites', 'domain_name')->ignore($site->{$this->idName}, $this->idName)
                    ]
                ]);

                /** @noinspection PhpUndefinedMethodInspection */
                $excepts = Schema::hasColumn('sites', 'site_url') ? $this->idName : [$this->idName, 'site_url'];

                $value = collect($value)->except($excepts)->toArray();

                $site->update($value);

                array_push($ids, $site->getKey());
            }
        });

        if ( ! empty($ids)) {
            /** @var Site[]|\Illuminate\Support\Collection $updatedSites */
            $updatedSites = Site::whereIn($this->idName, $ids)->get();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedSites);
    }

    /**
     * Update multiple site languages by site id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateSiteLanguagesBySiteId(Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $site);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.*.language_code' => 'required|string|distinct|exists:languages,code|exists:site_languages,language_code',
            'data.*.site_id' => [
                'sometimes',
                'string',
                Rule::in([$id])
            ],
            'data.*.is_main' => 'sometimes|is_boolean',
            'data.*.is_active' => 'sometimes|is_boolean'
        ]);

        $data = $request->input('data');

        $updatedSiteLanguages = [];
        $codes = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$codes, $site) {
            foreach ($data as $key => $value) {
                /** @var Language $language */
                $language = $site->languages()->findOrFail(strtolower($value['language_code']));

                $updateData = [];

                if (array_key_exists('is_active', $value)) {
                    $updateData['is_active'] = to_boolean($value['is_active']);
                }

                if (array_key_exists('is_main', $value)) {
                    $updateData['is_main'] = to_boolean($value['is_main']);

                    if (to_boolean($value['is_main'])) {
                        $updateData['is_active'] = true;

                        $languages = $site->languages;
                        foreach ($languages as $lang) {
                            $site->languages()->updateExistingPivot($lang->getKey(), ['is_main' => false]);
                        }
                    }
                }

                $site->languages()->updateExistingPivot($language->getKey(), $updateData);

                array_push($codes, $language->getKey());
            }
        });

        $site->selectNewMainLanguageIfNoneExists();

        if ( ! empty($codes)) {
            /** @var Language[]|\Illuminate\Support\Collection $updatedSiteLanguages */
            $updatedSiteLanguages = $site->languages()->whereIn('language_code', $codes)->get();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedSiteLanguages);
    }

    /**
     * Update a site by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $site);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.domain_name' => 'sometimes|string',
            'data.site_url' => 'sometimes|nullable|string',
            'data.description' => 'sometimes|nullable|string',
            'data.is_active' => 'sometimes|is_boolean'
        ]);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($site, $data) {
            $this->guardAgainstInvalidateRequest($data, [
                'data.domain_name' => [
                    'sometimes',
                    'required',
                    'string',
                    'min:3',
                    Rule::unique('sites', 'domain_name')->ignore($site->{$this->idName}, $this->idName)
                ]
            ]);

            /** @noinspection PhpUndefinedMethodInspection */
            $excepts = Schema::hasColumn('sites', 'site_url') ? $this->idName : [$this->idName, 'site_url'];

            $data = collect($data)->except($excepts)->toArray();

            $site->update($data);
        });

        /** @var Site $updated */
        $updated = $site->fresh();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Update a site language by its coded and site id
     *
     * @param Request $request
     * @param $siteId
     * @param $code
     * @return mixed
     */
    public function updateSiteLanguageBySiteIdAndCode(Request $request, $siteId, $code)
    {
        $code = strtolower($code);

        /** @var Site $site */
        $site = Site::findOrFail($siteId);

        /** @var Language $language */
        $language = $site->languages()->findOrFail($code);

        $this->authorizeForUser($this->auth->user(), 'update', $site);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.is_main' => 'sometimes|is_boolean',
            'data.is_active' => 'sometimes|is_boolean'
        ]);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($site, $language, $data) {
            $updateData = [];

            if (array_key_exists('is_active', $data)) {
                $updateData['is_active'] = to_boolean($data['is_active']);
            }

            if (array_key_exists('is_main', $data)) {
                $updateData['is_main'] = to_boolean($data['is_main']);

                if (to_boolean($data['is_main'])) {
                    $updateData['is_active'] = true;

                    /** @var Language[]|\Illuminate\Support\Collection $languages */
                    $languages = $site->languages;

                    /** @var Language $lang */
                    foreach ($languages as $lang) {
                        $site->languages()->updateExistingPivot($lang->getKey(), ['is_main' => false]);
                    }
                }
            }

            $site->languages()->updateExistingPivot($language->getKey(), $updateData);
        });

        $site->selectNewMainLanguageIfNoneExists();

        /** @var Language $updated */
        $updated = $site->languages()->findOrFail($code);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Delete multiple sites
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array',
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:sites,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var Site $site */
                $site = Site::findOrFail($value[$this->idName]);
                $this->authorizeForUser($this->auth->user(), 'delete', $site);
                $site->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Detach site languages by site id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function detachSiteLanguagesBySiteId(Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $site);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.*.language_code' => 'required|string|distinct|exists:languages,code|exists:site_languages,language_code'
        ]);

        $data = $request->input('data');
        $codes = collect($data)
            ->pluck('language_code')
            ->map(function ($code) {
                return strtolower($code);
            })
            ->all() ?: [];

        if ( ! empty($codes)) {
            /** @noinspection PhpUndefinedMethodInspection */
            DB::transaction(function () use ($site, $codes) {
                $site->languages()->detach($codes);

                // Cascade delete translations
                $languages = Language::whereIn('code', $codes)->get();
                $languages->each(function ($language) use ($site) {
                    /** @var Language $language */
                    $language->cascadeDeleteTranslations($site->id);
                });
            });
        }

        $site->selectNewMainLanguageIfNoneExists();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a site by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $site);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($site) {
            $site->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Detach a site language by its code and site id
     *
     * @param Request $request
     * @param $siteId
     * @param $code
     * @return mixed
     */
    public function detachSiteLanguageBySiteIdAndCode(/** @noinspection PhpUnusedParameterInspection */ Request $request, $siteId, $code)
    {
        /** @var Site $site */
        $site = Site::findOrFail($siteId);

        /** @var Language $language */
        $language = $site->languages()->findOrFail(strtolower($code));

        $this->authorizeForUser($this->auth->user(), 'update', $site);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($site, $language) {
            $site->languages()->detach($language->code);

            // Cascade delete translations
            $language->cascadeDeleteTranslations($site->id);
        });

        $site->selectNewMainLanguageIfNoneExists();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Reorder site languages by its site id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function reorderSiteLanguagesBySiteId(Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $site);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.*.language_code' => 'required|string|distinct|exists:languages,code|exists:site_languages,language_code',
            'data.*.display_order' => 'required|integer|distinct|min:1'
        ]);

        $data = $request->input('data');
        $dataCollection = collect($data);

        $codes = collect($data)
            ->pluck('language_code')
            ->map(function ($code) {
                return strtolower($code);
            })
            ->all() ?: [];

        /** @var Language[]|\Illuminate\Support\Collection $languages */
        $languages = $site->languages()->whereIn('language_code', $codes)->get();

        if ( ! empty($languages)) {
            /** @noinspection PhpUndefinedMethodInspection */
            DB::transaction(function () use ($dataCollection, $languages) {
                /** @var Language[]|\Illuminate\Support\Collection $languages */
                $languages->each(function ($language) use ($dataCollection) {
                    /**
                     * @var Language $language
                     * @var Language $filtered
                     */
                    if ($filtered = $dataCollection->where('language_code', $language->getKey())->first()) {
                        if ($displayOrder = collect($filtered)->get('display_order')) {
                            $language->pivot->display_order = $displayOrder;
                            $language->pivot->save();
                        }
                    }
                });
            });
        }

        /** @var Language[]|\Illuminate\Support\Collection $updated */
        $updated = $site->languages()->whereIn('language_code', $codes)->get();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Return a page preview as a html file
     *
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function getPagePreview(Request $request, $id)
    {
        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::findOrFail($id)
            ->makeHidden([
                'created_at',
                'updated_at'
            ]);

        if (empty($site->site_url)) {
            $baseApiUrl = config('cms.' . get_cms_application() . '.previews.' . $site->domain_name) . '/client-api/';
        } else {
            $baseApiUrl = preg_replace('/(\\/|\\\)+$/', '', $site->site_url) . '/client-api/';
        }

        if(is_null($baseApiUrl)) {
            $previews = config('cms.' . get_cms_application() . '.previews');
            if (empty($previews)) throw new \Exception(ErrorMessageConstants::SITE_NOT_FOUND);
            $baseApiUrl = array_values($previews)[0];
        }

        $this->guardAgainstInvalidateRequest($request->all(), [
            'friendly_url' => 'string|required|exists:pages',
            'language_code' => 'sometimes|string|nullable|min:2',
            'preview_data' => 'sometimes|nullable|array',
            'preview_data.page_data' => 'sometimes|nullable|array',
            'preview_data.global_item_data' => 'sometimes|nullable|array'
        ]);

        $friendlyUrl = $request->get('friendly_url');

        $languageCode = null;
        if ($request->except('language_code')) {
            $languageCode = $request->get('language_code');
        }

        $previewData = json_decode(json_encode($request->get('preview_data')), false);
        $previewPageData  = is_null($previewData)
            ? null
            : (isset($previewData->page_data)
                ? $previewData->page_data
                : null);
        $previewGlobalItemData  = is_null($previewData)
            ? null
            : (isset($previewData->global_item_data)
                ? $previewData->global_item_data
                : null);

        /** @noinspection PhpUndefinedMethodInspection */
        $site->languages = $site
            ->languages()
            ->wherePivot('is_active', true)
            ->with('siteTranslations')
            ->get()
            ->makeHidden([
                'created_at',
                'updated_at'
            ]);

        $site->main_language = $site
            ->languages()
            ->wherePivot('is_main', true)
            ->wherePivot('is_active', true)
            ->first()
            ->makeHidden([
                'created_at',
                'updated_at'
            ]);

        if (is_null($languageCode))  {
            /** @var Language $language */
            $language = $site
                ->languages()
                ->wherePivot('is_active', true)
                ->wherePivot('is_main', true)
                ->firstOrFail();

            $languageCode = $language->code;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $site
            ->languages()
            ->where('code', $languageCode)
            ->wherePivot('is_active', true)
            ->firstOrFail();

        $pageData = $site->generatePageData($friendlyUrl, $languageCode, $previewPageData);
        $globalItemData = $site->generateGlobalItemData($languageCode, $previewGlobalItemData);

        $client = new Client(['base_uri' => $baseApiUrl, 'verify' => false]);
        $response = $client->post(config('cms.' . get_cms_application() . '.preview_post_api', 'preview'), [
            'json' => [
                'site' => $site,
                'page_data' => $pageData,
                'global_item_data' => $globalItemData,
                'language_code' => $languageCode
            ],
            'stream' => true
        ]);

        $data = null;

        if ($response->getStatusCode() == 200) {
            $body = $response->getBody();
            while( ! $body->eof()) {
                $data .= $body->read(1024);
            }
        }

        return response()
            ->view('preview', ['preview' => html_entity_decode(htmlspecialchars($data))])
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * Return submissions' name and variable name
     *
     * @param Request $request
     * @param $id
     * @return array
     */
    public function getSubmissionNames(/** @noinspection PhpUnusedParameterInspection */Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);
        $lists = [];

        /** @noinspection PhpUndefinedMethodInspection */
        $submissions = DB::table('cms_logs')
            ->select(DB::raw('count(*) as total_count, SUBSTRING(log_data FROM POSITION(\'form_variable_name":"\' in log_data)+21 FOR POSITION(\'","form_type\' in log_data)-24) as variable_name'))
            ->where('action', LogConstants::FORM_SUBMIT)
            ->where('log_data', 'LIKE', '%"site_id":"' . $site->getKey() . '"%')
            ->groupBy('variable_name')
            ->orderBy('variable_name')
            ->get();

        if ( ! empty($submissions)) {
            $lists = collect($submissions)
                ->map(function ($submission) {
                    if ( ! isset($submission->variable_name)) return null;
                    $submission->name = preg_replace('/_/', ' ', ucwords($submission->variable_name, ' _\n'));
                    return $submission;
                })
                ->filter()
                ->all();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($lists);
    }

    /**
     * Return submission data
     *
     * @param Request $request
     * @param $id
     * @param null $variableName
     * @return mixed
     */
    public function getSubmission(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id, $variableName = null)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);
        $submissions = [];

        /** @var CmsLog[]|\Illuminate\Support\Collection $logs */
        $logs = CmsLog::where('action', LogConstants::FORM_SUBMIT)
            ->where('log_data', 'LIKE', '%"site_id":"' . $site->getKey() . '"%')
            ->orderBy('created_at', 'DESC')
            ->get();

        if ( ! empty($logs)) {
            if (is_null($variableName)) {
                $submissions = collect($logs)
                    ->map(function ($log) {
                        /** @var CmsLog $log */
                        $logData = json_recursive_decode($log->log_data);
                        $formVariableName = isset($logData->form_variable_name) ? $logData->form_variable_name : 'unknown';
                        $formType = preg_replace('/[\s_]+/', ' ', title_case($formVariableName));
                        return [
                            'form_variable_name' => $formVariableName,
                            'form_type' => $formType,
                            'submission_data' => $logData->submission_data ?: null,
                            'created_at' => $log->created_at
                        ];
                    })
                    ->filter()
                    ->groupBy('form_variable_name')
                    ->toArray();
            } else {
                $submissions = collect($logs)
                    ->filter(function ($log) use ($variableName) {
                        /** @var CmsLog $log */
                        $logData = json_recursive_decode($log->log_data);
                        return isset($logData->form_variable_name) && $logData->form_variable_name === $variableName;
                    })
                    ->map(function ($log) {
                        /** @var CmsLog $log */
                        $logData = json_recursive_decode($log->log_data);
                        $formVariableName = isset($logData->form_variable_name) ? $logData->form_variable_name : 'unknown';
                        $formType = preg_replace('/[\s_]+/', ' ', title_case($formVariableName));
                        return [
                            'form_variable_name' => $formVariableName,
                            'form_type' => $formType,
                            'submission_data' => $logData->submission_data ?: null,
                            'created_at' => $log->created_at
                        ];
                    })
                    ->filter()
                    ->values()
                    ->toArray();
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($submissions);
    }

    /**
     * Clear submissions
     *
     * @param Request $request
     * @param $id
     * @param null $variableName
     * @return mixed
     */
    public function clearSubmission(/** @noinspection PhpUnusedParameterInspection */Request $request, $id, $variableName = null)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($site, $variableName) {
            if (is_null($variableName)) {
                CmsLog::where('action', LogConstants::FORM_SUBMIT)
                    ->where('log_data', 'LIKE', '%"site_id":"' . $site->getKey() . '"%')
                    ->delete();
            } else {
                CmsLog::where('action', LogConstants::FORM_SUBMIT)
                    ->where('log_data', 'LIKE', '%"site_id":"' . $site->getKey() . '"%')
                    ->where('log_data', 'LIKE', '%"form_variable_name":"' . $variableName . '"%')
                    ->delete();
            }

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                DB::select('OPTIMIZE TABLE ' . (new CmsLog)->getTable());
            } catch (\Exception $exception) {}
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(true);
    }

    /**
     * Export submission by variable name into excel file
     *
     * @param Request $request
     * @param $id
     * @param $variableName
     * @return mixed
     */
    public function exportSubmission(/** @noinspection PhpUnusedParameterInspection */Request $request, $id, $variableName)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        /** @var GlobalItem $form */
        /** @noinspection PhpUndefinedMethodInspection */
        $form = $site
            ->globalItems()
            ->isActive()
            ->where('variable_name', $variableName)
            ->whereHas('globalItemOptions', function ($query) {
                /** @var \Illuminate\Database\Eloquent\Builder $query */
                $query->where('is_active', true)
                    ->whereIn('variable_name', ValidationRuleConstants::FORM_PRIVATE_PROPERTIES);
            })
            ->firstOrFail();

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection  $itemOptions */
        $itemOptions = $form->globalItemOptions;

        if (empty($itemOptions)) throw new ModelNotFoundException();

        $itemOptions = collect($itemOptions)
            ->transform(function ($itemOption) {
                /** @var GlobalItemOption $itemOption */
                return $itemOption->withNecessaryData()->all();
            })
            ->all();

        $properties = collect($itemOptions)
            ->where('variable_name', ValidationRuleConstants::FORM_PROPERTIES)
            ->first();

        $controlListData = array_key_exists('translated_text', $properties)
            ? json_recursive_decode($properties['translated_text'])
            : json_recursive_decode($properties['option_value']);

        $controlListData = collect($controlListData)->sortBy(function ($controlList) {
            return $controlList->order;
        })->all();

        /** @var Page[] $pages */
        $pages = $site->pages()->get();

        $extractedProperties = extract_control_list_data($site, $controlListData, $properties['id'], null, null, $pages);

        $propertyNames = collect($extractedProperties)
            ->pluck('name')
            ->all();

        $propertyLabels = collect($extractedProperties)
            ->pluck('label')
            ->all();

        /** @var CmsLog[]|\Illuminate\Support\Collection $logs */
        $logs = CmsLog::where('action', LogConstants::FORM_SUBMIT)
            ->where('log_data', 'LIKE', '%"site_id":"' . $site->getKey() . '"%')
            ->orderBy('created_at', 'DESC')
            ->get();

        $submissions = [];

        if ( ! empty($logs) && ! empty($propertyNames)) {
            $submissions = collect($logs)
                ->filter(function ($log) use ($variableName) {
                    /** @var CmsLog $log */
                    $logData = json_recursive_decode($log->log_data);
                    return isset($logData->form_variable_name) && $logData->form_variable_name === $variableName;
                })
                ->map(function ($log) use ($propertyNames) {
                    /** @var CmsLog $log */
                    $logData = json_recursive_decode($log->log_data);
                    $submissionData = $logData->submission_data ?: null;
                    $columns = self::getSubmissionColumns($submissionData, $propertyNames);
                    $columns[LogConstants::EXCEL_SUBMISSION_DATE] = $log->created_at;
                    return $columns;
                })
                ->filter()
                ->values()
                ->toArray();
        }

        $file = $this->submissionExcelFile->export($propertyLabels, $submissions);

        $timestamp = Carbon::now()->toCookieString();

        $response = [
            'name' => 'Form submission - ' . $variableName . ' (' . $timestamp . ').xlsx',
            'file' => 'data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,' . base64_encode($file)
        ];

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($response);
    }

    /**
     * @param $submission
     * @param $names
     * @return array
     */
    private function getSubmissionColumns($submission, $names)
    {
        return collect($names)
            ->reduce(function ($array, $name) use ($submission) {
                $value = isset($submission->{$name})
                    ? $submission->{$name}->value ?: null
                    : null;
                $array[$name] = $value;
                return $array;
            }, []);
    }

    /**
     * Return a site translation excel file
     *
     * @param Request $request
     * @param $id
     */
    public function exportTranslations(/** @noinspection PhpUnusedParameterInspection */Request $request, $id)
    {
        /** @var Site $site */
        $site = Site::findOrFail($id);

        $file = $this->siteTranslationExcelFile->export($site);

        $timestamp = Carbon::now()->toCookieString();

        $response = [
            'name' => ucwords($site->domain_name) . ' Translations (' . $timestamp . ').xlsx',
            'file' => 'data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64,' . base64_encode($file)
        ];

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($response);
    }

    /**
     * Import site translation excel file
     *
     * @param Request $request
     * @param $id
     */
    public function importTranslations(Request $request, $id)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'file' => 'file|required'
        ]);

        /** @var Site $site */
        $site = Site::findOrFail($id);

        $file = $request->file('file');

        $this->siteTranslationExcelFile->import($site, $file);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(true);
    }
}
