<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\HelperConstants;
use App\Api\Constants\LogConstants;
use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\ValidationRuleConstants;
use App\Api\Events\FormEmailSent;
use App\Api\Models\CmsLog;
use App\Api\Models\ComponentOption;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\Language;
use App\Api\Models\Page;
use App\Api\Models\PageItemOption;
use App\Api\Models\Site;
use App\Api\Models\SiteTranslation;
use App\Api\Models\TemplateItemOption;
use App\Api\Tools\Query\QueryFactory;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HelperController extends BaseController
{
    /**
     * HelperController constructor.
     */
    function __construct()
    {
        $this->middleware('api-form-token', [
            'only' => [
                'getFormPropertyData',
                'saveFormPropertyData',
                'getPaginationFromItemOption'
            ]
        ]);
    }

    /**
     * Return site data
     *
     * @param Request $request
     * @param $domainName
     * @return mixed
     */
    public function getSiteData(/** @noinspection PhpUnusedParameterInspection */ Request $request, $domainName)
    {
        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail()
            ->makeHidden([
                'created_at',
                'updated_at'
            ]);

        /** @noinspection PhpUndefinedMethodInspection */
        $site->languages = $site
            ->languages()
            ->wherePivot('is_active', true)
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

        $geoipEnabledValue = false;

        /** GEOIP */
        /** @var GlobalItem $geoipEnabled */
        if ($geoipEnabled = $site
            ->globalItems()
            ->isActive()
            ->where('variable_name', 'geoip_enabled')
            ->first()) {

            /** @var GlobalItemOption $value */
            if ($value = $geoipEnabled
                ->globalItemOptions()
                ->isActive()
                ->where('variable_name', 'value')
                ->first()) {

                $geoipValue = $value->withNecessaryData()->all();
                $geoipEnabledValue = array_key_exists('translated_text', $geoipValue)
                    ? $geoipValue['translated_text']
                    : $geoipValue['option_value'];
                $geoipEnabledValue = to_boolean($geoipEnabledValue);
            }
        }

        $site->geoip_enabled = $geoipEnabledValue;

        $googleReCaptchaSiteKey = config('cms.' . get_cms_application() . '.google_recaptcha_site_key');
        $googleReCaptchaSecret = config('cms.' . get_cms_application() . '.google_recaptcha_secret');

        if ( ! empty($googleReCaptchaSiteKey) && ! empty($googleReCaptchaSecret)) {
            $site->google_recaptcha_site_key = $googleReCaptchaSiteKey;
        } else {
            $site->google_recaptcha_site_key = null;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($site);
    }

    /**
     * Return a redirect url by a source url
     *
     * @param Request $request
     * @param $domainName
     * @throws \Exception
     * @return mixed
     */
    public function getRedirectUrlBySourceUrl(Request $request, $domainName)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'source_url' => 'string'
        ]);

        $sourceUrl = $request->input('source_url');

        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

        /** @noinspection PhpUndefinedMethodInspection */
        $redirectUrl = $site->redirectUrls()
            ->where('source_url', $sourceUrl)
            ->isActive()
            ->first();

        if (is_null($redirectUrl)) {
            /** @var Language|Language[]|\Illuminate\Support\Collection $siteLanguages */
            $siteLanguages = $site->languages;
            $codes = $siteLanguages->pluck('code')->all();
            $regex = '^\/?(' . join('|', $codes) . ')';

            $tempSourceUrl = preg_replace('/' . $regex . '/', '', $sourceUrl);

            if (empty($tempSourceUrl)) {
                $tempSourceUrl = 'homepage';
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $redirectUrl = $site->redirectUrls()
                ->where('source_url', $tempSourceUrl)
                ->isActive()
                ->first();

            if (is_null($redirectUrl) && !! preg_match('/' . $regex . '/', $sourceUrl, $matches)) {
                $lang = preg_replace('/^\//', '', $matches[0]);
                // check dir
                $redirectUrls = $site->redirectUrls()
                    ->where('source_url', 'like', $lang . '%')
                    ->orWhere('source_url', 'like', '/' . $lang . '%')
                    ->isActive()
                    ->get();

                if (count($redirectUrls) <= 0) {
                    throw new \Exception('Redirect not found.');
                }

                $sourceUrlWithSlash = '/' . preg_replace('/^\//', '', $sourceUrl);

                $redirectUrl = collect($redirectUrls)
                    ->filter(function($url) use ($sourceUrlWithSlash) {
                        $urlWithSlash = '/' . preg_replace('/^\//', '', $url->source_url);
                        return fnmatch($urlWithSlash, $sourceUrlWithSlash) ?: false;
                    })
                    ->first();

                if (is_null($redirectUrl)) {
                    throw new \Exception('Redirect not found.');
                }
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($redirectUrl);
    }

    /**
     * Return site translations
     *
     * @param Request $request
     * @param $domainName
     * @param null $languageCode
     * @return mixed
     */
    public function getSiteTranslations(/** @noinspection PhpUnusedParameterInspection */ Request $request, $domainName, $languageCode = null)
    {
        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

        $siteTranslations = collect([]);

        if (is_null($languageCode)) {
            /** @var Language[]|\Illuminate\Support\Collection $siteLanguages */
            $siteLanguages = $site
                ->languages()
                ->wherePivot('is_active', true)
                ->get();

            if ( ! empty($siteLanguages)) {
                $siteLanguages->each(function ($item) use (&$siteTranslations) {
                    /** @var Language $item */
                    $siteTranslations[$item->code] = $item->siteTranslations->makeHidden(['created_at', 'updated_at']);
                });
            }
        } else {
            /** @var Language $siteLanguage */
            $siteLanguage = $site
                ->languages()
                ->wherePivot('language_code', $languageCode)
                ->wherePivot('is_active', true)
                ->first();

            if ( ! is_null($siteLanguage)) {
                $siteTranslations = $siteLanguage->siteTranslations->makeHidden(['created_at', 'updated_at']);
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($siteTranslations);
    }

    /**
     * Return site translations by item option id
     *
     * @param Request $request
     * @param $domainName
     * @param $itemOptionId
     * @param null $languageCode
     * @return mixed
     * @throws \Exception
     */
    public function getSiteTranslationByItemOptionId(/** @noinspection PhpUnusedParameterInspection */ Request $request, $domainName, $itemOptionId, $languageCode = null)
    {
        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

        $translation = null;

        if (is_null($languageCode)) {
            /** @var SiteTranslation[]|\Illuminate\Support\Collection $siteTranslations */
            $siteTranslations = SiteTranslation::where('item_id', $itemOptionId)->get();

            if (empty($siteTranslations)) {
                if ($option = get_item_option_by_any_item_option_id($itemOptionId)) {
                    $optionWithSiteTranslation = $option->withOptionSiteTranslation($languageCode);
                    $translation = $optionWithSiteTranslation['translated_text'];
                } else {
                    throw new \Exception(ErrorMessageConstants::ITEM_OPTION_NOT_FOUND);
                }
            } else {
                if ($itemType = $siteTranslations->first()->item_type) {
                    /** @var \Illuminate\Database\Eloquent\Model|\App\Api\Models\BaseModel $itemOptionClass */
                    $itemOptionClass = get_class_by_name($itemType, 'App\\Api\\Models');

                    /** @var PageItemOption|TemplateItemOption|GlobalItemOption|ComponentOption $option */
                    $option = $itemOptionClass::where('is_active', true)
                        ->where($itemOptionClass->getKeyName(), $itemOptionId)
                        ->firstOrFail();

                    if ($parentSite = $option->getParentSite()) {
                        if ( ! $site->is($parentSite)) {
                            throw new \Exception(ErrorMessageConstants::FROM_DIFFERENT_SITE);
                        }
                    } else {
                        if (isset($option->siteRequired) && $option->siteRequired) {
                            throw new \Exception(ErrorMessageConstants::SITE_NOT_FOUND);
                        }
                    }
                } else {
                    throw new \Exception(ErrorMessageConstants::FROM_DIFFERENT_SITE);
                }

                $translation = $siteTranslations->pluck('translated_text', 'language_code');
            }
        } else {
            /** @var SiteTranslation $siteTranslation */
            if ($siteTranslation = SiteTranslation::where('item_id', $itemOptionId)
                ->where('language_code', $languageCode)
                ->first()) {

                if ($itemType = $siteTranslation->item_type) {
                    /** @var \Illuminate\Database\Eloquent\Model|\App\Api\Models\BaseModel $itemOptionClass */
                    $itemOptionClass = get_class_by_name($itemType, 'App\\Api\\Models');

                    /** @var PageItemOption|TemplateItemOption|GlobalItemOption|ComponentOption $option */
                    $option = $itemOptionClass::where('is_active', true)
                        ->where($itemOptionClass->getKeyName(), $itemOptionId)
                        ->firstOrFail();

                    if ($parentSite = $option->getParentSite()) {
                        if ( ! $site->is($parentSite)) {
                            throw new \Exception(ErrorMessageConstants::FROM_DIFFERENT_SITE);
                        }
                    } else {
                        if (isset($option->siteRequired) && $option->siteRequired) {
                            throw new \Exception(ErrorMessageConstants::SITE_NOT_FOUND);
                        }
                    }

                    if ( ! is_null($siteTranslation)) {
                        $translation = $siteTranslation->translated_text;
                    }
                } else {
                    throw new \Exception(ErrorMessageConstants::FROM_DIFFERENT_SITE);
                }
            } else {
                if ($option = get_item_option_by_any_item_option_id($itemOptionId)) {
                    $optionWithSiteTranslation = $option->withOptionSiteTranslation($languageCode);
                    $translation = $optionWithSiteTranslation['translated_text'];
                } else {
                    throw new \Exception(ErrorMessageConstants::ITEM_OPTION_NOT_FOUND);
                }
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($translation);
    }

    /**
     * Return page data
     *
     * @param Request $request
     * @param $domainName
     * @return mixed
     */
    public function getPageData(Request $request, $domainName)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'friendly_url' => 'string|required|exists:pages',
            'language_code' => 'string|nullable|min:2',
            'filter_items' => 'sometimes|nullable|array'
        ]);

        $friendlyUrl = $request->get('friendly_url');
        $languageCode = $request->get('language_code');
        $filterItems = $request->get('filter_items', []);

        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

        if (is_null($languageCode))  {
            /** @var Language $language */
            $language = $site
                ->languages()
                ->wherePivot('is_active', true)
                ->wherePivot('is_main', true)
                ->firstOrFail(['code']);

            $languageCode = $language->code;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $site
            ->languages()
            ->where('code', $languageCode)
            ->wherePivot('is_active', true)
            ->firstOrFail();

        $pageData = $site->generatePageData($friendlyUrl, $languageCode, null, $filterItems);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($pageData);
    }

    /**
     * Return parent pages data
     *
     * @param Request $request
     * @param $domainName
     * @return mixed
     */
    public function getParentPagesDataByFriendlyUrl(Request $request, $domainName)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'friendly_url' => 'string|required|exists:pages',
            'language_code' => 'string|nullable|min:2',
            'filter_pages' => 'sometimes|nullable|array',
            'filter_items' => 'sometimes|nullable|array',
            'order_by' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['updated_at', 'created_at', 'published_at'])
            ],
            'order_direction' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['asc', 'desc', 'ASC', 'DESC'])
            ],
        ]);

        $friendlyUrl = $request->get('friendly_url');
        $languageCode = $request->get('language_code');
        $filterPages = $request->get('filter_pages', []);
        $filterItems = $request->get('filter_items', []);
        $orderBy = $request->get('order_by', 'updated_at');
        $orderDirection = $request->get('order_direction', 'DESC');

        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

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

        $today = Carbon::today('UTC');

        /** @var \Illuminate\Database\Eloquent\Builder $pageQuery */
        /** @noinspection PhpUndefinedMethodInspection */
        $pageQuery = $site
            ->pages()
            ->isActive()
            ->where(DB::raw('BINARY `friendly_url`'), $friendlyUrl);

        /** @noinspection PhpUndefinedMethodInspection */
        // Backward compatible to the database that does not have published at column
        if (Schema::hasColumn('pages', 'published_at')) {
            $pageQuery = $pageQuery->where(function ($query) use ($today) {
                /** @var $query \Illuminate\Database\Eloquent\Builder */
                $query->where('published_at', null)
                    ->orWhere('published_at', '<=', $today->toDateString());
            });
        }

        /** @var Page $page */
        $page = $pageQuery->firstOrFail();

        /** @var \Illuminate\Database\Eloquent\Builder $parentPageQuery */
        if (empty($filterPages)) {
            $parentPageQuery = $page
                ->parents()
                ->isActive();
        } else {
            /** @noinspection PhpUndefinedMethodInspection */
            $parentPageQuery = $page
                ->parents()
                ->isActive()
                ->whereIn(DB::raw('BINARY `friendly_url`'), $filterPages);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        // Backward compatible to the database that does not have published at column
        if (Schema::hasColumn('pages', 'published_at')) {
            $parentPageQuery = $parentPageQuery->where(function ($query) use ($today) {
                /** @var $query \Illuminate\Database\Eloquent\Builder */
                $query->where('published_at', null)
                    ->orWhere('published_at', '<=', $today->toDateString());
            });
        }

        /** @var Page[]|\Illuminate\Support\Collection $parentPages */
        $parentPages = $parentPageQuery
            ->orderBy($orderBy, $orderDirection)
            ->orderBy('name')
            ->get();

        $result = [];

        $parentPages->each(function ($page) use ($site, $languageCode, $filterItems, &$result) {
            /** @var Page $page */
            $result[] = $site->generatePageData($page->friendly_url, $languageCode, null, $filterItems);
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($result);
    }

    /**
     * Return child pages data
     *
     * @param Request $request
     * @param $domainName
     * @return mixed
     */
    public function getChildPagesDataByFriendlyUrl(Request $request, $domainName)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'friendly_url' => 'string|required|exists:pages',
            'language_code' => 'string|nullable|min:2',
            'filter_pages' => 'sometimes|nullable|array',
            'filter_items' => 'sometimes|nullable|array',
            'order_by' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['updated_at', 'created_at', 'published_at'])
            ],
            'order_direction' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['asc', 'desc', 'ASC', 'DESC'])
            ],
        ]);

        $friendlyUrl = $request->get('friendly_url');
        $languageCode = $request->get('language_code');
        $filterPages = $request->get('filter_pages', []);
        $filterItems = $request->get('filter_items', []);
        $orderBy = $request->get('order_by', 'updated_at');
        $orderDirection = $request->get('order_direction', 'DESC');

        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

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

        $today = Carbon::today('UTC');

        /** @var \Illuminate\Database\Eloquent\Builder $pageQuery */
        /** @noinspection PhpUndefinedMethodInspection */
        $pageQuery = $site
            ->pages()
            ->isActive()
            ->where(DB::raw('BINARY `friendly_url`'), $friendlyUrl);

        /** @noinspection PhpUndefinedMethodInspection */
        // Backward compatible to the database that does not have published at column
        if (Schema::hasColumn('pages', 'published_at')) {
            $pageQuery = $pageQuery->where(function ($query) use ($today) {
                /** @var $query \Illuminate\Database\Eloquent\Builder */
                $query->where('published_at', null)
                    ->orWhere('published_at', '<=', $today->toDateString());
            });
        }

        /** @var Page $page */
        $page = $pageQuery->firstOrFail();

        /** @var \Illuminate\Database\Eloquent\Builder $childPageQuery */
        if (empty($filterPages)) {
            $childPageQuery = $page
                ->children()
                ->isActive();
        } else {
            /** @noinspection PhpUndefinedMethodInspection */
            $childPageQuery = $page
                ->children()
                ->isActive()
                ->whereIn(DB::raw('BINARY `friendly_url`'), $filterPages);
        }

        /** @noinspection PhpUndefinedMethodInspection */
        // Backward compatible to the database that does not have published at column
        if (Schema::hasColumn('pages', 'published_at')) {
            $childPageQuery = $childPageQuery->where(function ($query) use ($today) {
                /** @var $query \Illuminate\Database\Eloquent\Builder */
                $query->where('published_at', null)
                    ->orWhere('published_at', '<=', $today->toDateString());
            });
        }

        /** @var Page[]|\Illuminate\Support\Collection $parentPages */
        $childPages = $childPageQuery
            ->orderBy($orderBy, $orderDirection)
            ->orderBy('name')
            ->get();

        $result = [];

        $childPages->each(function ($page) use ($site, $languageCode, $filterItems, &$result) {
            /** @var Page $page */
            $result[] = $site->generatePageData($page->friendly_url, $languageCode, null, $filterItems);
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($result);
    }

    /**
     * Return global item data
     *
     * @param Request $request
     * @param $domainName
     * @param null $languageCode
     * @return mixed
     */
    public function getGlobalItemData(/** @noinspection PhpUnusedParameterInspection */ Request $request, $domainName, $languageCode = null)
    {
        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

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

        $globalItemData = $site->generateGlobalItemData($languageCode);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($globalItemData);
    }

    /**
     * Return pages by categories
     *
     * @param Request $request
     * @param $domainName
     * @return mixed
     */
    public function getPagesByCategories(Request $request, $domainName)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'categories' => 'required',
            'page_id' => 'sometimes|nullable|string|exists:pages,id',
            'amount' => 'sometimes|nullable|integer|min:1',
            'direction' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in([HelperConstants::PREVIOUS_DIRECTION, HelperConstants::NEXT_DIRECTION])
            ],
            'order_by' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['published_at', 'created_at', 'updated_at', 'name', 'variable_name', 'id'])
            ],
            'order_direction' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['asc', 'desc', 'ASC', 'DESC'])
            ],
            'language_code' => 'sometimes|nullable|string|exists:site_languages,language_code',
            'filter_items' => 'sometimes|nullable|array'
        ]);

        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

        $languageCode = $request->get('language_code');
        $pageId = $request->get('page_id');
        $amount = $request->get('amount');
        $direction = $request->get('direction', HelperConstants::PREVIOUS_DIRECTION);
        $orderBy = $request->get('order_by', 'updated_at');
        $orderDirection = $request->get('order_direction', 'DESC');
        $filterItems = $request->get('filter_items', []);

        if (is_null($languageCode))  {
            /** @var Language $language */
            $language = $site
                ->languages()
                ->wherePivot('is_active', true)
                ->wherePivot('is_main', true)
                ->firstOrFail();

            $languageCode = $language->code;
        }

        $categories = $request->get('categories', []);

        if ( ! is_array($categories)) $categories = array($categories);

        $categories = array_map('strtoupper', $categories);
        $today = Carbon::today('UTC');

        /** @var Page[]|\Illuminate\Support\Collection $pages */
        $pages = collect([]);

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $site
            ->pristinePages()
            ->isActive();

        /** @noinspection PhpUndefinedMethodInspection */
        // Backward compatible to the database that does not have published at column
        if (Schema::hasColumn('pages', 'published_at')) {
            $query = $query->where(function ($query) use ($today) {
                /** @var $query \Illuminate\Database\Eloquent\Builder */
                $query->where('published_at', null)
                    ->orWhere('published_at', '<=', $today->toDateString());
            });
        }

        $query = $query->whereHas('categoryNames', function ($query) use ($categories) {
                /** @var \Illuminate\Database\Eloquent\Builder $query */
                $query->whereIn('name', $categories);
            });

        if (is_null($pageId)) {
            if ( ! is_null($amount)) {
                $query = $query->take($amount);
            }

            $pages = $query
                ->orderBy($orderBy, $orderDirection)
                ->orderBy('name')
                ->get();
        } else {
            $tempPages = $query
                ->orderBy($orderBy, $orderDirection)
                ->orderBy('name')
                ->get();

            $tempPagesCount = count($tempPages);

            if ($tempPagesCount > 0) {
                $index = collect($tempPages)->search(function ($page) use ($pageId) {
                    return $page->id === $pageId;
                });

                if ($index !== false) {
                    $offset = ($direction === HelperConstants::PREVIOUS_DIRECTION)
                        ? $index - 1
                        : $index + 1;

                    if ($offset >= 0 && $offset < $tempPagesCount) {
                        if (is_null($amount)) {
                            $pages = collect($tempPages)->slice($offset)->all();
                        } else {
                            $pages = collect($tempPages)->slice($offset, $amount)->all();
                        }
                    } else if ($offset < 0) {
                        $reversed = collect($tempPages)->reverse()->toArray();

                        if (is_null($amount)) {
                            $pages = collect($reversed)->slice(abs($offset))->all();
                        } else {
                            $pages = collect($tempPages)->slice(abs($offset), $amount)->all();
                        }
                    } else if ($offset >= $tempPagesCount) {
                        if (is_null($amount)) {
                            $pages = collect($tempPages)->slice($offset % $tempPagesCount)->all();
                        } else {
                            $pages = collect($tempPages)->slice($offset % $tempPagesCount, $amount)->all();
                        }
                    }
                }
            }
        }

        $result = [];

        collect($pages)->each(function ($page) use ($site, $languageCode, $filterItems, &$result) {
            /** @var Page $page */
            $result[] = $site->generatePageData($page->friendly_url, $languageCode, null, $filterItems);
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($result);
    }

    /**
     * Return pages by template variable name
     * @param Request $request
     * @param $domainName
     * @return mixed
     */
    public function getPagesByTemplate(Request $request, $domainName)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'template_name' => 'required',
            'page_id' => 'sometimes|nullable|string|exists:pages,id',
            'amount' => 'sometimes|nullable|integer|min:1',
            'direction' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in([HelperConstants::PREVIOUS_DIRECTION, HelperConstants::NEXT_DIRECTION])
            ],
            'order_by' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['published_at', 'created_at', 'updated_at', 'name', 'variable_name', 'id'])
            ],
            'order_direction' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['asc', 'desc', 'ASC', 'DESC'])
            ],
            'language_code' => 'sometimes|nullable|string|exists:site_languages,language_code',
            'filter_items' => 'sometimes|nullable|array'
        ]);

        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

        $languageCode = $request->get('language_code');
        $pageId = $request->get('page_id');
        $amount = $request->get('amount');
        $direction = $request->get('direction', HelperConstants::PREVIOUS_DIRECTION);
        $orderBy = $request->get('order_by', 'updated_at');
        $orderDirection = $request->get('order_direction', 'DESC');
        $filterItems = $request->get('filter_items', []);

        if (is_null($languageCode))  {
            /** @var Language $language */
            $language = $site
                ->languages()
                ->wherePivot('is_active', true)
                ->wherePivot('is_main', true)
                ->firstOrFail();

            $languageCode = $language->code;
        }

        $templateName = $request->get('template_name');

        if ( ! is_array($templateName)) $templateName = array($templateName);

        $today = Carbon::today('UTC');

        /** @var Page[]|\Illuminate\Support\Collection $pages */
        $pages = collect([]);

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $site
            ->pristinePages()
            ->isActive();

        /** @noinspection PhpUndefinedMethodInspection */
        // Backward compatible to the database that does not have published at column
        if (Schema::hasColumn('pages', 'published_at')) {
            $query = $query->where(function ($query) use ($today) {
                /** @var $query \Illuminate\Database\Eloquent\Builder */
                $query->where('published_at', null)
                    ->orWhere('published_at', '<=', $today->toDateString());
            });
        }

        $query = $query->whereHas('template', function ($query) use ($templateName) {
                /** @var \Illuminate\Database\Eloquent\Builder $query */
                $query->whereIn('variable_name', $templateName);
            });

        if (is_null($pageId)) {
            if ( ! is_null($amount)) {
                $query = $query->take($amount);
            }

            $pages = $query
                ->orderBy($orderBy, $orderDirection)
                ->orderBy('name')
                ->get();
        } else {
            $tempPages = $query
                ->orderBy($orderBy, $orderDirection)
                ->orderBy('name')
                ->get();

            $tempPagesCount = count($tempPages);

            if ($tempPagesCount > 0) {
                $index = collect($tempPages)->search(function ($page) use ($pageId) {
                    return $page->id === $pageId;
                });

                if ($index !== false) {
                    $offset = ($direction === HelperConstants::PREVIOUS_DIRECTION)
                        ? $index - 1
                        : $index + 1;

                    if ($offset >= 0 && $offset < $tempPagesCount) {
                        if (is_null($amount)) {
                            $pages = collect($tempPages)->slice($offset)->all();
                        } else {
                            $pages = collect($tempPages)->slice($offset, $amount)->all();
                        }
                    } else if ($offset < 0) {
                        $reversed = collect($tempPages)->reverse()->toArray();

                        if (is_null($amount)) {
                            $pages = collect($reversed)->slice(abs($offset))->all();
                        } else {
                            $pages = collect($tempPages)->slice(abs($offset), $amount)->all();
                        }
                    } else if ($offset >= $tempPagesCount) {
                        if (is_null($amount)) {
                            $pages = collect($tempPages)->slice($offset % $tempPagesCount)->all();
                        } else {
                            $pages = collect($tempPages)->slice($offset % $tempPagesCount, $amount)->all();
                        }
                    }
                }
            }
        }

        $result = [];

        collect($pages)->each(function ($page) use ($site, $languageCode, $filterItems, &$result) {
            /** @var Page $page */
            $result[] = $site->generatePageData($page->friendly_url, $languageCode, null, $filterItems);
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($result);
    }

    /**
     * Return site map data
     *
     * @param Request $request
     * @param $domainName
     * @return mixed
     */
    public function getSiteMap(Request $request, $domainName)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'exceptions' => 'sometimes|nullable|array'
        ]);

        $exceptions = $request->input('exceptions', []);
        $rules = [];

        if ( ! empty($exceptions)) {
            foreach ($exceptions as $key => $value) {
                $rules['exceptions.' . $key] = 'string|exists:pages,friendly_url';
            }

            $this->guardAgainstInvalidateRequest($request->all(), $rules);
        }

        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

        $siteMap = $site->getSiteMap($exceptions);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($siteMap);
    }

    /**
     * Return site map as an xml file of main site language
     *
     * @param Request $request
     * @param $domainName
     * @return mixed
     */
    public function generateMainSiteMapXML(/** @noinspection PhpUnusedParameterInspection */ Request $request, $domainName)
    {
        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

        $content = $site->generateSiteMapXML();

        $content = <<<XML_STRING
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<!-- created with QUIQ CMS of QUO (www.quo-global.com) -->
$content
</urlset>
XML_STRING;

        /** @noinspection PhpUndefinedMethodInspection */
        return response($content)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Return site map as an xml file
     *
     * @param Request $request
     * @param $domainName
     * @param $languageCode
     * @return mixed
     */
    public function generateSiteMapXML(/** @noinspection PhpUnusedParameterInspection */ Request $request, $domainName, $languageCode)
    {
        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

        $content = $site->generateSiteMapXML($languageCode);

        $content = <<<XML_STRING
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:xhtml="http://www.w3.org/1999/xhtml"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<!-- created with QUIQ CMS of QUO (www.quo-global.com) -->
$content
</urlset>
XML_STRING;

        /** @noinspection PhpUndefinedMethodInspection */
        return response($content)
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Return form data
     *
     * @param Request $request
     * @param $domainName
     * @param $variableName
     * @param null $languageCode
     * @return mixed
     */
    public function getFormPropertyData(/** @noinspection PhpUnusedParameterInspection */ Request $request, $domainName, $variableName, $languageCode = null)
    {
        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

        /** @var GlobalItem $form */
        /** @noinspection PhpUndefinedMethodInspection */
        $form = $site
            ->globalItems()
            ->where('variable_name', $variableName)
            ->whereHas('globalItemOptions', function ($query) {
                /** @var \Illuminate\Database\Eloquent\Builder $query */
                $query->whereIn('variable_name', ValidationRuleConstants::FORM_PRIVATE_PROPERTIES);
            })
            ->firstOrFail();

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

        /** @var GlobalItemOption $properties */
        /** @noinspection PhpUndefinedMethodInspection */
        $properties = $form
            ->globalItemOptions()
            ->where('variable_name', ValidationRuleConstants::FORM_PROPERTIES)
            ->firstOrFail();

        /** @var array|GlobalItemOption $properties */
        $properties = $properties->withNecessaryData([], $languageCode)->all();

        $controlListData = array_key_exists('translated_text', $properties)
            ? json_recursive_decode($properties['translated_text'])
            : json_recursive_decode($properties['option_value']);

        $formData = collect($controlListData)
            ->map(function ($item) {
                if (isset($item->props)) {
                    if ($properties = $item->props) {
                        return collect($properties)
                            ->map(function ($property) {
                                if (isset($property->element_type) && isset($property->option_value)) {
                                    if ($property->element_type === OptionElementTypeConstants::CHECKBOX) {
                                        $property->option_value = to_boolean($property->option_value);
                                    }
                                }

                                return $property;
                            })
                            ->pluck('option_value', 'variable_name')
                            ->all();
                    }
                }

                return null;

            })
            ->filter()
            ->all();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($formData);
    }

    /**
     * Save form data
     *
     * @param Request $request
     * @param $domainName
     * @param $variableName
     * @param null|string $languageCode
     * @return mixed
     * @throws \Exception
     */
    public function saveFormPropertyData(Request $request, $domainName, $variableName, $languageCode = null)
    {
        $requestData = $request->all();

        if (array_key_exists(ValidationRuleConstants::HONEY_POT_FIELD, $requestData)) {
            if ( ! empty($requestData[ValidationRuleConstants::HONEY_POT_FIELD])) {
                throw new \Exception(ErrorMessageConstants::SPAM_BOT_DETECTED);
            }

            unset($requestData[ValidationRuleConstants::HONEY_POT_FIELD]);
        }

        $googleReCaptchaSecret = config('cms.' . get_cms_application() . '.google_recaptcha_secret');

        if (array_key_exists(ValidationRuleConstants::GOOGLE_RECAPTCHA_RESPONSE, $requestData) && ! empty($googleReCaptchaSecret)) {
            $client = new Client([
                'base_uri' => 'https://www.google.com/recaptcha/api/',
                'verify' => false
            ]);

            $reCaptchaResponse = $client->post('siteverify', [
                'query' => [
                    'secret' => $googleReCaptchaSecret,
                    'response' => $requestData[ValidationRuleConstants::GOOGLE_RECAPTCHA_RESPONSE]
                ]
            ]);

            if ($reCaptchaResponse->getStatusCode() === 200) {
                $reCaptchaData = json_decode($reCaptchaResponse->getBody());

                if ( ! isset($reCaptchaData->success)) {
                    throw new \Exception('Google reCAPTCHA verification unknown response data');
                }

                if ( ! $reCaptchaData->success) {
                    throw new \Exception('Google reCAPTCHA mismatched verification');
                }
            } else {
                throw new \Exception('Google reCAPTCHA verification failed.');
            }

            unset($requestData[ValidationRuleConstants::GOOGLE_RECAPTCHA_RESPONSE]);
        }

        /** @var Site $site */
        /** @noinspection PhpUndefinedMethodInspection */
        $site = Site::where('domain_name', $domainName)
            ->isActive()
            ->firstOrFail();

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
            ->transform(function ($itemOption) use ($languageCode) {
                /** @var GlobalItemOption $itemOption */
                return $itemOption->withNecessaryData([], $languageCode)->all();
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

        $only = collect($extractedProperties)
            ->pluck('name')
            ->all();

        /**
         * Overrides
         */
        $overrides = [
            ValidationRuleConstants::FORM_OVERRIDE_SENDER_EMAIL_KEY => null,
            ValidationRuleConstants::FORM_OVERRIDE_RECIPIENT_EMAILS_KEY => null,
            ValidationRuleConstants::FORM_OVERRIDE_CC_EMAILS_KEY => null,
            ValidationRuleConstants::FORM_OVERRIDE_BCC_EMAILS_KEY => null
        ];

        if (array_key_exists(ValidationRuleConstants::FORM_OVERRIDE_SENDER_EMAIL, $requestData)) {
            if ( ! empty($requestData[ValidationRuleConstants::FORM_OVERRIDE_SENDER_EMAIL])) {
                $overrides[ValidationRuleConstants::FORM_OVERRIDE_SENDER_EMAIL_KEY] = $requestData[ValidationRuleConstants::FORM_OVERRIDE_SENDER_EMAIL];

                if (preg_match('/,/', $overrides[ValidationRuleConstants::FORM_OVERRIDE_SENDER_EMAIL_KEY])) {
                    throw new \Exception(ErrorMessageConstants::OVERRIDE_SENDER_EMAIL_CANNOT_BE_MULTIPLE);
                }
            }

            unset($requestData[ValidationRuleConstants::FORM_OVERRIDE_SENDER_EMAIL]);
        }

        if (array_key_exists(ValidationRuleConstants::FORM_OVERRIDE_RECIPIENT_EMAILS, $requestData)) {
            if ( ! empty($requestData[ValidationRuleConstants::FORM_OVERRIDE_RECIPIENT_EMAILS])) {
                $overrides[ValidationRuleConstants::FORM_OVERRIDE_RECIPIENT_EMAILS_KEY] = $requestData[ValidationRuleConstants::FORM_OVERRIDE_RECIPIENT_EMAILS];
            }

            unset($requestData[ValidationRuleConstants::FORM_OVERRIDE_RECIPIENT_EMAILS]);
        }

        if (array_key_exists(ValidationRuleConstants::FORM_OVERRIDE_CC_EMAILS, $requestData)) {
            if ( ! empty($requestData[ValidationRuleConstants::FORM_OVERRIDE_CC_EMAILS])) {
                $overrides[ValidationRuleConstants::FORM_OVERRIDE_CC_EMAILS_KEY] = $requestData[ValidationRuleConstants::FORM_OVERRIDE_CC_EMAILS];
            }

            unset($requestData[ValidationRuleConstants::FORM_OVERRIDE_CC_EMAILS]);
        }

        if (array_key_exists(ValidationRuleConstants::FORM_OVERRIDE_BCC_EMAILS, $requestData)) {
            if ( ! empty($requestData[ValidationRuleConstants::FORM_OVERRIDE_BCC_EMAILS])) {
                $overrides[ValidationRuleConstants::FORM_OVERRIDE_BCC_EMAILS_KEY] = $requestData[ValidationRuleConstants::FORM_OVERRIDE_BCC_EMAILS];
            }

            unset($requestData[ValidationRuleConstants::FORM_OVERRIDE_BCC_EMAILS]);
        }

        /** @noinspection PhpUnusedParameterInspection */
        $submissionData = collect($only)
            ->map(function ($name) use ($controlListData, $requestData, $variableName) {
                $value = null;

                if (array_key_exists($name, $requestData)) {
                    $value = $requestData[$name];

                    if ($value instanceof UploadedFile) {
                        $value = $this->quickUpload($value, $variableName);
                    } else if (is_array($value)) {
                        $data = array_filter($value, function ($v) {
                            return $v instanceof UploadedFile;
                        });

                        if ( ! empty($data)) {
                            $value = $this->quickUpload($data, $variableName);
                        }
                    }
                }

                $data = collect($controlListData)
                    ->map(function ($item) {
                        $properties = $item->props;

                        return collect($properties)
                            ->pluck('option_value', 'variable_name')
                            ->all();
                    })
                    ->where('name', $name)
                    ->first();

                $data['value'] = $value;

                return $data;
            })
            ->keyBy('name')
            ->all();

        $formType = collect($itemOptions)
            ->where('variable_name', 'form_type')
            ->first();

        $formTypeValue = ( ! empty($formType)) ? $formType['option_value'] : null;

        $logData = [
            'form_variable_name' => $variableName,
            'form_type' => $formTypeValue,
            'site_id' => $site->getKey(),
            'submission_data' => $submissionData
        ];
        /** @var CmsLog $log */
        $log = CmsLog::log($logData, LogConstants::FORM_SUBMIT);

        event(new FormEmailSent($site, $log, $itemOptions, $submissionData, $formTypeValue, $overrides));

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(true);
    }

    /**
     * Return a control-list query data
     *
     * @param Request $request
     * @param $itemOptionId
     * @throws \Exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function getControlListQuery(Request $request, $itemOptionId)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            OptionElementTypeConstants::PAGINATION_PAGE_NAME => 'sometimes|integer',
            OptionElementTypeConstants::FILTER_NAME => 'sometimes|string',
            HelperConstants::LANGUAGE_CODE => 'sometimes|nullable|string|exists:languages,code'
        ]);

        $isSubItem = false;
        if (preg_match('/\./', $itemOptionId)) {
            $ids = explode('.', $itemOptionId);
            $id = array_shift($ids);
            $isSubItem = true;
        } else {
            $id = $itemOptionId;
        }

        /** @var PageItemOption|GlobalItemOption|TemplateItemOption|ComponentOption|Null $option */
        $option = get_item_option_by_any_item_option_id($id);

        $site = $option->getParentSite();

        $this->guardAgainstInactiveItemOptionParentsUntilSite($option);

        $languageCode = $request->input(HelperConstants::LANGUAGE_CODE);
        $page = $request->input(OptionElementTypeConstants::PAGINATION_PAGE_NAME, 1);
        $filterJSON = $request->input(OptionElementTypeConstants::FILTER_NAME, '');
        $filter = json_decode($filterJSON);

        $renderData = $request->input(OptionElementTypeConstants::PAGINATION_RENDER_DATA, []);

        $optionArray = $option->withNecessaryData([], $languageCode)->all();

        $controlListData = array_key_exists('translated_text', $optionArray)
            ? json_recursive_decode($optionArray['translated_text'])
            : json_recursive_decode($optionArray['option_value']);

        /** @var Page[] $pages */
        $pages = $site->pages()->get();

        $data = extract_control_list_data($site, $controlListData, $option->getKey(), $languageCode, $itemOptionId, $pages);

        if ($isSubItem) {
            $items = $data['option_value'];
            $elementType = $data['element_type'];
            $elementValue = $data['element_value'];
            $queryId = $data['query_id'];
        } else {
            $items = $data;
            $elementType = $optionArray['element_type'];
            $elementValue = $optionArray['element_value'];
            $queryId = $option->getKey();
        }

        $result = null;
        $isResultJSON = true;

        if ($query = QueryFactory::make(
            $queryId,
            $items,
            $elementType,
            $elementValue,
            $site,
            $languageCode,
            $page,
            $filter
        )) {
            $query->setIncludeData(true);
            $queryJSON = $query->jsonSerialize();

            if ( ! $request->ajax() ) {
                if ($query->isPagination()) {
                    $previews = config('cms.' . get_cms_application() . '.previews');

                    if (empty($previews)) throw new \Exception(ErrorMessageConstants::SITE_NOT_FOUND);

                    $baseApiUrl = array_values($previews)[0];

                    $client = new Client(['base_uri' => $baseApiUrl, 'verify' => false]);
                    $response = $client->post('render-paginator', [
                        'json' => [
                            'query' => $query,
                            'data' => $renderData
                        ],
                        'stream' => true
                    ]);

                    $data = null;

                    if ($response->getStatusCode() == 200) {
                        $body = $response->getBody();
                        while ( ! $body->eof()) {
                            $data .= $body->read(1024);
                        }
                    }

                    $result = $data;
                    $isResultJSON = false;
                } else {
                    $isResultJSON = true;
                    $result = $queryJSON;
                }
            }
        } else {
            $queryJSON = null;
        }

        if ($request->ajax()) {
            return response()->json($queryJSON);
        } else {
            if ($isResultJSON) {
                return response()->json($result);
            }
            return response($result);
        }
    }

    public function getCacheLinks(Request $request)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        if ( ! Schema::hasColumn('sites', 'site_url')) {
            /** @noinspection PhpUndefinedMethodInspection */
            return response()->apiJson([]);
        }

        $siteQuery = Site::whereNotNull('site_url');
        $siteId = $request->input('sid');

        if ( ! is_null($siteId)) {
            $siteQuery = $siteQuery->where('id', $siteId);
        }

        /** @var Site[] $sites */
        $sites = $siteQuery->with(
                [
                'pages' => function ($query) {
                        /** @var \Illuminate\Database\Eloquent\Builder $query */
                        return $query->where('is_active', true)
                            ->orderBy('updated_at', 'DESC')
                            ->orderBy('name');
                    },
                ]
            )
            ->get();

        $links = [];

        if (count($sites) <= 0) {
            /** @noinspection PhpUndefinedMethodInspection */
            return response()->apiJson($links);
        }

        $links = collect($sites)->flatMap(function ($site) {
            /** @var Site $site */
            if (count($site->pages) <= 0 || empty($site->site_url)) return null;

            $siteUrl = preg_replace('/(\\/|\\\)+$/', '', $site->site_url) . '/';

                return collect($site->pages)
                    ->map(function ($page) use ($siteUrl) {
                /** @var Page $page */
                        return [
                            'template_id' => $page->template_id,
                            'friendly_url' => $siteUrl . $page->friendly_url
                        ];
                    })
                    ->toArray();
            })
            ->filter()
            ->groupBy('template_id')
            ->sortBy(function ($item) {
                return count($item);
            })
            ->flatMap(function ($item) {
                return collect($item)->pluck('friendly_url')->toArray();
            })
            ->toArray();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($links);
    }

    /**
     * Return (array of) a saved path of uploaded files
     *
     * @param $files
     * @param null $directory
     * @return bool|mixed|string
     */
    private function quickUpload($files, $directory = null)
    {
        if (empty($files)) return false;

        /** @var \Illuminate\Http\UploadedFile[] $items */
        $items = is_array($files)
            ? $files
            : array($files);

        $return = [];

        $path = config('cms.' . get_cms_application() . '.uploads_path') . '/' . config('cms.' . get_cms_application() . '.uploaded_files_path');

        if ( ! is_null($directory) || ! empty($directory)) {
            $path .= '/' . $directory;
        }

        $uploadsPath = trim_uploads_path($path);

        /** @var \Illuminate\Http\UploadedFile $item */
        foreach ($items as $item) {
            if (isset($item)) {
                $fileName = $item->getClientOriginalName();
                $fileName = preg_replace('/\s+/', '_', $fileName);

                $trimmedPath = $uploadsPath . '/' . preg_replace('/^\/|\\\/', '', trim_uploads_path($fileName));

                /** @noinspection PhpUndefinedMethodInspection */
                if (Storage::exists($trimmedPath)) {
                    $dirName = pathinfo($trimmedPath, PATHINFO_DIRNAME);
                    $name = pathinfo($trimmedPath, PATHINFO_FILENAME);
                    $name = preg_replace('/\s+/', '_', $name);
                    $extension = pathinfo($trimmedPath, PATHINFO_EXTENSION);
                    $random = str_random();

                    /** @noinspection PhpUndefinedMethodInspection */
                    do {
                        $trimmedPath = $dirName . '/' . $name . '_' . $random . '.' . $extension;
                    } while (Storage::exists($trimmedPath));
                }

                $dirName = pathinfo($trimmedPath, PATHINFO_DIRNAME);
                $baseName = pathinfo($trimmedPath, PATHINFO_BASENAME);

                if ($dirName === '.') {
                    $dirName = '';
                }
                /** @noinspection PhpUndefinedMethodInspection */
                $savedPath = Storage::putFileAs(
                    $dirName, $item, $baseName
                );

                if (app()->environment('testing')) {
                    $savedPath = HelperConstants::UPLOADS_FOLDER_TESTING . '/' . $savedPath;
                } else {
                    $savedPath = HelperConstants::UPLOADS_FOLDER . '/' . $savedPath;
                }

                $return[] = url($savedPath);
            }
        }

        return is_array($files) ? $return : $return[0];
    }
}
