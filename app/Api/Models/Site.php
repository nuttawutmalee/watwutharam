<?php

namespace App\Api\Models;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\HelperConstants;
use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Tools\Query\QueryFactory;
use App\Api\Traits\IsActive;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Rutorika\Sortable\BelongsToSortedManyTrait;

/**
 * Class Site
 *
 * @package App\Api\Models
 *
 * @property string $id
 *
 * @property string $domain_name
 *
 * @property string $site_url
 *
 * @property string $description
 *
 * @property bool $is_active
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property Template|Template[] $templates
 *
 * @property RedirectUrl|RedirectUrl[] $redirectUrls
 *
 * @property GlobalItem|GlobalItem[] $globalItems
 *
 * @property Page|Page[] $pages
 *
 * @property Language|Language[] $languages
 *
 * @property Language $main_language
 *
 * @property SiteLanguage $pivot
 *
 * @property bool $geoip_enabled
 *
 * @property string $google_recaptcha_site_key
 *
 */
class Site extends BaseModel
{
    use IsActive, BelongsToSortedManyTrait;

    /**
     * Active global items
     *
     * @var $activeGlobalItems
     */
    private $activeGlobalItems;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'domain_name',
        'site_url',
        'description',
        'is_active'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with templates
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Template|Template[]
     */
    public function templates()
    {
        return $this->hasMany('App\Api\Models\Template', 'site_id');
    }

    /**
     * Return a relationship with redirect urls
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|RedirectUrl|RedirectUrl[]
     */
    public function redirectUrls()
    {
        return $this->hasMany('App\Api\Models\RedirectUrl', 'site_id');
    }

    /**
     * Return a relationship with global items
     *
     * @return mixed|\Illuminate\Database\Eloquent\Relations\HasMany|GlobalItem|GlobalItem[]
     */
    public function globalItems()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasMany('App\Api\Models\GlobalItem', 'site_id')->orderBy('display_order');
    }

    /**
     * Return a relationship with global items
     *
     * @return mixed|\Illuminate\Database\Eloquent\Relations\HasMany|GlobalItem|GlobalItem[]
     */
    public function pristineGlobalItems()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasMany('App\Api\Models\GlobalItem', 'site_id');
    }

    /**
     * Return a relationship with pages
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough|Page|Page[]
     */
    public function pages()
    {
        return $this->hasManyThrough(
            'App\Api\Models\Page', 'App\Api\Models\Template',
            'site_id', 'template_id', 'id')->orderBy('updated_at', 'desc');
    }

    /**
     * Return a relationship with pages
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough|Page|Page[]
     */
    public function pristinePages()
    {
        return $this->hasManyThrough(
            'App\Api\Models\Page', 'App\Api\Models\Template',
            'site_id', 'template_id', 'id');
    }

    /**
     * Return a relationship with languages
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|Language|Language[]
     */
    public function languages()
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return $this
            ->belongsToSortedMany('App\Api\Models\Language', 'display_order', 'site_languages', 'site_id', 'language_code', 'languages')
            ->withPivot('display_order', 'is_active', 'is_main')
            ->withTimestamps();
    }

    /**
     * Overrides
     */

    /**
     * Override delete function
     *
     * @param bool $cascadeRedirectUrls
     * @param bool $cascadeTemplate
     * @param bool $cascadeGlobalItem
     * @param bool $cascadeLanguageIntermediateTable
     * @return bool|null
     */
    public function delete($cascadeRedirectUrls = true, $cascadeTemplate = true, $cascadeGlobalItem = true, $cascadeLanguageIntermediateTable = true)
    {
        if ($cascadeRedirectUrls) {
            if (count($this->redirectUrls) > 0) {
                foreach ($this->redirectUrls as $redirectUrl) {
                    $redirectUrl->delete();
                }
            }
        }

        if ($cascadeTemplate) {
            if (count($this->templates) > 0) {
                foreach ($this->templates as $template) {
                    $template->delete();
                }
            }
        }

        if ($cascadeGlobalItem) {
            if (count($this->globalItems) > 0) {
                foreach ($this->globalItems as $globalItem) {
                    $globalItem->delete();
                }
            }
        }

        if ($cascadeLanguageIntermediateTable) {
            $this->languages()->detach();
        }

        return parent::delete();
    }

    /**
     * Return a belongs-to relationship's name
     *
     * @return string
     */
    protected function getBelongsToManyCaller()
    {
        return 'languages';
    }

    /**
     * Override a new pivot function
     *
     * @param Model $parent
     * @param array $attributes
     * @param string $table
     * @param bool $exists
     * @param null $using
     * @return SiteLanguage
     */
    public function newPivot(Model $parent, array $attributes, $table, $exists, $using = null)
    {
        return new SiteLanguage($parent, $attributes, $table, $exists);
    }

    /**
     * Custom functions
     */

    /**
     * Select new main language if none exists
     *
     * @return void
     */
    public function selectNewMainLanguageIfNoneExists()
    {
        /** @var Language[] $mainLanguage */
        if ($mainLanguage = $this->fresh('languages')->languages()->wherePivot('is_main', '=', true)->get()) {
            if (count($mainLanguage) > 1) {

                /** @var Language $lang */
                foreach ($mainLanguage as $lang) {
                    $this->fresh('languages')->languages()->updateExistingPivot($lang->getKey(), ['is_main' => false]);
                }

                /** @var Language $first */
                $first = collect($mainLanguage)->first();
                $this->fresh('languages')->languages()->updateExistingPivot($first->getKey(), [
                    'is_main' => true,
                    'is_active' => true
                ]);
            } else if (count($mainLanguage) <= 0) {

                /** @var Language[] $siteLanguages */
                if ($siteLanguages = $this->fresh('languages')->languages) {
                    if (count($siteLanguages) > 0) {
                        if ($english = $this->fresh('languages')->languages()->where('code', 'en')->first()) {

                            /** @var Language $english */
                            $this->fresh('languages')->languages()->updateExistingPivot($english->getKey(), [
                                'is_main' => true,
                                'is_active' => true
                            ]);
                        } else if ($first = $this->fresh('languages')->languages()->first()) {

                            /** @var Language $first */
                            $this->fresh('languages')->languages()->updateExistingPivot($first->getKey(), [
                                'is_main' => true,
                                'is_active' => true
                            ]);
                        }
                    } else {
                        if ($main = Language::find(config('cms.' . get_cms_application() . '.main_language.code'))) {

                            /** @var Language $main */
                            $syncData[$main->code] = ['is_main' => true, 'is_active' => true];
                            $this->fresh('languages')->languages()->sync($syncData);
                        } else {

                            /** @var Language $english */
                            $english = Language::firstOrCreate([
                                'code' => 'en',
                                'name' => 'English',
                                'locale' => 'en-GB',
                                'hreflang' => 'en'
                            ]);

                            $syncData[$english->code] = ['is_main' => true, 'is_active' => true];
                            $this->fresh('languages')->languages()->sync($syncData);
                        }
                    }
                }
            }
        }
    }

    /**
     * Return a page data
     *
     * @param $friendlyUrl
     * @param null $languageCode
     * @param null $previewData
     * @param array $filterItems
     * @return array|null
     * @throws \Exception
     */
    public function generatePageData($friendlyUrl, $languageCode = null, $previewData = null, $filterItems = [])
    {
        $pageKeyName = (new Page)->getKeyName();
        $today = Carbon::today('UTC');

        /** @var \Illuminate\Database\Eloquent\Builder $pageQuery */
        /** @noinspection PhpUndefinedMethodInspection */
        $pageQuery = $this
            ->pages()
            ->where(DB::raw('BINARY `friendly_url`'), $friendlyUrl) // Fix sensitive case for mysql
            ->isActive();

        /** @noinspection PhpUndefinedMethodInspection */
        // Backward compatible to the database that does not have published at column
        if (Schema::hasColumn('pages', 'published_at')) {
            $pageQuery = $pageQuery->where(function ($query) use ($today) {
                /** @var $query \Illuminate\Database\Eloquent\Builder */
                $query->where('published_at', null)
                    ->orWhereDate('published_at', '<=', $today->toDateString());
            });
        }

        /** @var Page $page */
        $page = $pageQuery->first();

        if (is_null($page)) {
            throw new \Exception(ErrorMessageConstants::PAGE_NOT_FOUND);
        }

        /** @var Page[] $pages */
        $pages = $this->pages()->get();

        /** @var Template $template */
        $template = $page->template;

        $pageItemData = $page->getPageItemData($this, $languageCode, $previewData, $filterItems, $pages);

        $categories = [];
        $categoryNames = $page->categoryNames;

        if (count($categoryNames) > 0) {
            $categories = collect($categoryNames)->pluck('name')->all();
        }

        $parents = [];

        /** @var \Illuminate\Database\Eloquent\Builder $parentPageQuery */
        $parentPageQuery = $page
            ->parents()
            ->isActive();

        /** @noinspection PhpUndefinedMethodInspection */
        // Backward compatible to the database that does not have published at column
        if (Schema::hasColumn('pages', 'published_at')) {
            $parentPageQuery = $parentPageQuery->where(function ($query) use ($today) {
                /** @var $query \Illuminate\Database\Eloquent\Builder */
                $query->where('published_at', null)
                    ->orWhere('published_at', '<=', $today->toDateString());
            });
        }

        /** @var Page[] $parentPages */
        $parentPages = $parentPageQuery->orderBy('updated_at', 'desc')
            ->orderBy('name')
            ->get();

        if (count($parentPages) > 0) {

            /** @var Page $parentPage */
            foreach ($parentPages as $parentPage) {
                if ($parentPage->is_active) {
                    $categories_parents = [];
                    $categoryParentNames = $parentPage->categoryNames;

                    if (count($categoryParentNames) > 0) {
                        $categories_parents = collect($categoryParentNames)->pluck('name')->all();
                    }

                    $parents[] = [
                        "{$pageKeyName}" => $parentPage->getKey(),
                        'name' => $parentPage->name,
                        'variable_name' => $parentPage->variable_name,
                        'friendly_url' => $parentPage->friendly_url,
                        'template' => $parentPage->template->variable_name,
                        HelperConstants::HELPER_CREATED_AT => $parentPage->created_at,
                        HelperConstants::HELPER_UPDATED_AT => $parentPage->updated_at,
                        HelperConstants::HELPER_PUBLISHED_AT => $parentPage->published_at,
                        HelperConstants::HELPER_CATEGORIES => $categories_parents,
                        HelperConstants::HELPER_PARENTS => [],
                        HelperConstants::HELPER_CHILDREN => []
                    ];
                }

            }
        }

        $children = [];

        /** @var \Illuminate\Database\Eloquent\Builder $childPageQuery */
        $childPageQuery = $page
            ->children()
            ->isActive();

        /** @noinspection PhpUndefinedMethodInspection */
        // Backward compatible to the database that does not have published at column
        if (Schema::hasColumn('pages', 'published_at')) {
            $childPageQuery = $childPageQuery->where(function ($query) use ($today) {
                /** @var $query \Illuminate\Database\Eloquent\Builder */
                $query->where('published_at', null)
                    ->orWhere('published_at', '<=', $today->toDateString());
            });
        }

        /** @var Page[] $childrenPages */
        $childrenPages = $childPageQuery->orderBy('updated_at', 'desc')
            ->orderBy('name')
            ->get();

        if (count($childrenPages) > 0) {

            /** @var Page $childrenPage */
            foreach ($childrenPages as $childrenPage) {
                if ($childrenPage->is_active) {
                    $categories_children = [];
                    $categoryChildrenNames = $childrenPage->categoryNames;

                    if (count($categoryChildrenNames) > 0) {
                        $categories_children = collect($categoryChildrenNames)->pluck('name')->all();
                    }

                    $children[] = [
                        "{$pageKeyName}" => $childrenPage->getKey(),
                        'name' => $childrenPage->name,
                        'variable_name' => $childrenPage->variable_name,
                        'friendly_url' => $childrenPage->friendly_url,
                        'template' => $childrenPage->template->variable_name,
                        HelperConstants::HELPER_CREATED_AT => $childrenPage->created_at,
                        HelperConstants::HELPER_UPDATED_AT => $childrenPage->updated_at,
                        HelperConstants::HELPER_PUBLISHED_AT => $childrenPage->published_at,
                        HelperConstants::HELPER_CATEGORIES => $categories_children,
                        HelperConstants::HELPER_PARENTS => [],
                        HelperConstants::HELPER_CHILDREN => []
                    ];
                }
            }
        }

        $pageData = [
            "{$pageKeyName}" => $page->getKey(),
            'name' => $page->name,
            'variable_name' => $page->variable_name,
            'friendly_url' => $page->friendly_url,
            'template' => $template->variable_name,
            HelperConstants::HELPER_CREATED_AT => $page->created_at,
            HelperConstants::HELPER_UPDATED_AT => $page->updated_at,
            HelperConstants::HELPER_PUBLISHED_AT => $page->published_at,
            HelperConstants::HELPER_CATEGORIES => $categories,
            HelperConstants::HELPER_PARENTS => $parents,
            HelperConstants::HELPER_CHILDREN => $children,
        ];

        $pageData = array_merge($pageData, $pageItemData);

        return $pageData;
    }

    /**
     * Return a global item data
     *
     * @param null $languageCode
     * @param null $previewData
     * @return null
     */
    public function generateGlobalItemData($languageCode = null, $previewData = null)
    {
        $globalItemData = null;

        /** @var GlobalItem|GlobalItem[] $globalItems */
        $globalItems = $this->getActiveGlobalItems();

        if ( ! empty($globalItems)) {
            /** @var Page[] $pages */
            $pages = $this->pages()->get();

            /** @var GlobalItem $globalItem */
            foreach ($globalItems as $globalItem) {
                $optionData = [];

                /** @var GlobalItemOption[] $globalItemOptions */
                $globalItemOptions = $globalItem->globalItemOptions;
                $globalItemWithCategories = $globalItem->withNecessaryData()->all();

                if ( ! empty($globalItemOptions)) {
                    foreach ($globalItemOptions as $globalItemOption) {
                        $return = [];
                        $queryJSON = null;

                        $optionArray = $globalItemOption->withNecessaryData([], $languageCode)->all();

                        if ($optionArray['element_type'] === OptionElementTypeConstants::CONTROL_LIST) {
                            $controlListData = array_key_exists('translated_text', $optionArray)
                                ? json_recursive_decode($optionArray['translated_text'])
                                : json_recursive_decode($optionArray['option_value']);

                            $previewValue = extract_control_list_data($this, $controlListData, $globalItemOption->getKey(), $languageCode, null, $pages);

                            if ( ! empty($previewData) && ! is_null($previewData)) {
                                try {
                                    $previewValue = isset($previewData->{$globalItem->variable_name}->{$optionArray['variable_name']})
                                        ? extract_control_list_data($this, $previewData->{$globalItem->variable_name}->{$optionArray['variable_name']}, $globalItemOption->getKey(), $languageCode, null, $pages)
                                        : $previewValue;
                                } catch (\Exception $e) {}
                            }

                            if ($query = QueryFactory::make(
                                $globalItemOption->getKey(),
                                $previewValue,
                                $optionArray['element_type'],
                                $optionArray['element_value'],
                                $this,
                                $languageCode
                            )) {
                                $queryJSON = $query->jsonSerialize();
                                $previewValue = [];
                            }
                        } else {
                            $previewValue = array_key_exists('translated_text', $optionArray)
                                ? $optionArray['translated_text']
                                : $optionArray['option_value'];

                            if ( ! empty($previewData) && ! is_null($previewData)) {
                                try {
                                    $previewValue = isset($previewData->{$globalItem->variable_name}->{$optionArray['variable_name']})
                                        ? $previewData->{$globalItem->variable_name}->{$optionArray['variable_name']}
                                        : $previewValue;
                                } catch (\Exception $e) {}
                            }
                        }

                        switch ($optionArray['option_type']) {
                            case OptionValueConstants::INTEGER:
                            $previewValue = intval($previewValue);
                                break;
                            case OptionValueConstants::DECIMAL:
                            $previewValue = floatval($previewValue);
                                break;
                            default:
                                break;
                        }

                        $elementValue = json_recursive_decode($optionArray['element_value']);

                        if (isset($elementValue->propStructure) && is_array($elementValue->propStructure)) {
                            $structures = array_map(function ($structure) {
                                if (isset($structure->element_value) && is_string($structure->element_value)) {
                                    $structure->element_value = json_recursive_decode($structure->element_value);
                                }
                                return $structure;
                            }, $elementValue->propStructure);

                            $elementValue->propStruture = $structures;
                        }

                        switch ($optionArray['element_type']) {
                            case OptionElementTypeConstants::CHECKBOX:
                                $previewValue = to_boolean($previewValue);
                                break;
                            case OptionElementTypeConstants::TEXTBOX:
                                if (isset($elementValue->helper) && $elementValue->helper === 'url') {
                                    if (is_uuid($previewValue)) {
                                        $pageSelectedId = $previewValue;
                                    } else if (isset($elementValue->selectedPageId) && is_uuid($elementValue->selectedPageId)) {
                                        $pageSelectedId = $elementValue->selectedPageId;
                                    } else {
                                        $pageSelectedId = null;
                                    }

                                    if ($pageSelectedId) {
                                    /** @var Page $targetPage */
                                        if ($targetPage = collect($pages)->where('id', $pageSelectedId)->first()) {
                                        $previewValue = $targetPage->friendly_url;
                                        }
                                    }
                                }
                                break;
                            default:
                                break;
                        }

                        $return[$optionArray['variable_name']] = $previewValue;
                        $return[$optionArray['variable_name'] . HelperConstants::HELPER_ID] = $optionArray['id'];
                        $return[$optionArray['variable_name'] . HelperConstants::HELPER_OPTION_TYPE] = $optionArray['option_type'];
                        $return[$optionArray['variable_name'] . HelperConstants::HELPER_ELEMENT_TYPE] = $optionArray['element_type'];
                        $return[$optionArray['variable_name'] . HelperConstants::HELPER_ELEMENT_VALUE] = $elementValue;

                        if ( ! is_null($queryJSON)) {
                            $return[$optionArray['variable_name'] . HelperConstants::HELPER_QUERY] = $queryJSON;
                        }

                        if ( ! empty($return)) {
                            $optionData = array_merge($return, $optionData);
                        }
                    }
                }

                $optionData[HelperConstants::HELPER_CATEGORIES] = $globalItemWithCategories['categories'];

                $displayOrder = $globalItemWithCategories['display_order'];
                if ( ! empty($previewData) && ! is_null($previewData)) {
                    try {
                        $displayOrder = isset($previewData->{$globalItem->variable_name}->{HelperConstants::HELPER_DISPLAY_ORDER})
                            ? $previewData->{$globalItem->variable_name}->{HelperConstants::HELPER_DISPLAY_ORDER}
                            : $globalItemWithCategories['display_order'];
                    } catch (\Exception $e) {}
                }

                $optionData[HelperConstants::HELPER_ID] = $globalItem->id;
                $optionData[HelperConstants::HELPER_DISPLAY_ORDER] = $displayOrder;
                $optionData[HelperConstants::HELPER_VARIABLE_NAME] = $globalItem->variable_name;
                $optionData[HelperConstants::HELPER_CREATED_AT] = $globalItem->created_at;
                $optionData[HelperConstants::HELPER_UPDATED_AT] = $globalItem->updated_at;
                $optionData[HelperConstants::HELPER_TEMPLATE] = (is_null($globalItem->component))
                    ? $globalItem->variable_name
                    : $globalItem->component->variable_name;
                $globalItemData[$globalItem['variable_name']] = $optionData;
            }
        }

        return $globalItemData;
    }

    /**
     * Return a site map data
     *
     * @param array $exceptions
     * @return array
     */
    public function getSiteMap($exceptions = [])
    {
        $today = Carbon::today('UTC');
        $siteMap = [];

        /** @var \Illuminate\Database\Eloquent\Builder $pagesQuery */
        $pagesQuery = $this
            ->pages()
            ->isActive();

        /** @noinspection PhpUndefinedMethodInspection */
        // Backward compatible to the database that does not have published at column
        if (Schema::hasColumn('pages', 'published_at')) {
            $pagesQuery = $pagesQuery->where(function ($query) use ($today) {
                /** @var $query \Illuminate\Database\Eloquent\Builder */
                $query->where('published_at', null)
                    ->orWhere('published_at', '<=', $today->toDateString());
            });
        }

        /** @var Page[] $pages */
        $pages = $pagesQuery->with(
                [
                    'template' => function ($query) {
                        /** @var $query \Illuminate\Database\Eloquent\Builder */
                        $query->without('site');
                    }
                ],
                'parents'
            )
            ->orderBy('friendly_url')
            ->get()
            ->makehidden([
                'site_id',
                'is_active'
            ]);

        /** @var Page $page */
        foreach ($pages as $page) {
            if ( ! in_array($page->friendly_url, $exceptions)) {
                $page->children = $this->getRecursivePageChildren($pages, $page->getKey(), $exceptions);
                $siteMap[] = $page;
            }
        }

        if ( ! empty($siteMap)) {
            $siteMap = collect($siteMap)
                ->sort(function ($a, $b) {

                    /**
                     * @var Page $a
                     * @var Page $b
                     */
                    if ($a->friendly_url === 'homepage') return -1;
                    if ($b->friendly_url === 'homepage') return 1;
                    return 0;
                })
                ->values()
                ->all();
        }

        return $siteMap;
    }

    /**
     * Return a site map as an xml
     *
     * @param $languageCode
     * @return string
     * @throws \Exception
     */
    public function generateSiteMapXML($languageCode = null)
    {
        if (empty($this->site_url)) {
        $siteUrl = config('cms.' . get_cms_application() . '.previews.' . $this->domain_name);
            $siteUrl = preg_replace('/(\/)client-api(\/?)$/', '/', $siteUrl);
        } else {
            $siteUrl = $this->site_url;
        }

        if (is_null($siteUrl) || empty($siteUrl)) {
            throw new \Exception(ErrorMessageConstants::PREVIEW_DOMAIN_NOT_FOUND);
        }

        $xml = '';
        $pages = $this->getSiteMap();

        if (is_null($languageCode)) {
            $siteLanguage = $this
                ->languages()
                ->wherePivot('is_main', true)
                ->wherePivot('is_active', true)
                ->first();
        } else {
            /** @var Language $siteLanguage */
            $siteLanguage = $this
                ->languages()
                ->where('code', $languageCode)
                ->wherePivot('is_active', true)
                ->first();
        }

        if ( ! is_null($siteLanguage)) {
            if (is_null($languageCode)) {
                $languageCode = $siteLanguage->code;
            }

            /** @var Language[] $siteLanguages */
            $siteLanguages = $this->languages()->wherePivot('is_active', true)->get();

            $hreflangs = [];

            if ( ! empty($siteLanguages)) {
                foreach ($siteLanguages as $language) {
                    $hreflangs[$language->code] = (empty($language->hreflang))
                        ? ['hreflang' => $language->code, 'is_main' => $language->pivot->is_main]
                        : ['hreflang' => $language->hreflang, 'is_main' => $language->pivot->is_main];
                }
            }

            if ( ! empty($pages)) {
                foreach ($pages as $page) {
                    $temp = '';
                    $priority = ($page->friendly_url === 'homepage') ? '1.00' : '0.80';
                    $friendlyUrl = ($page->friendly_url === 'homepage') ? '' : $page->friendly_url;
                    $href = $siteLanguage->pivot->is_main
                        ? htmlspecialchars($siteUrl . $friendlyUrl)
                        : htmlspecialchars($siteUrl . $siteLanguage->code . '/' . $friendlyUrl);

                    $href = preg_replace('/(\\|\/)+$/', '', $href);

                    $temp .= '<url>' . PHP_EOL;
                    $temp .= '<loc>' . $href . '</loc>' . PHP_EOL;
                    $temp .= '<priority>' . $priority . '</priority>' . PHP_EOL;
                    $temp .= '<lastmod>' . $page->updated_at->toW3cString() . '</lastmod>';

                    if ( ! empty($hreflangs)) {
                        foreach ($hreflangs as $key => $options) {
                            if ($key === $languageCode && ! is_null($languageCode)) continue;

                            $link = $options['is_main']
                                ? htmlspecialchars($siteUrl . $friendlyUrl)
                                : htmlspecialchars($siteUrl . $key . '/' . $friendlyUrl);

                            $temp .= '<xhtml:link rel="alternate" hreflang="' . $options['hreflang'] . '" href="' . $link . '"/>' . PHP_EOL;
                        }
                    }

                    $temp .= '</url>' . PHP_EOL;

                    $xml .= $temp;
                }
            }

            return $xml;
        } else {
            throw new \Exception('Site language not found');
        }
    }

    /**
     * Return a site map's page data with children data
     *
     * @param $pages
     * @param $id
     * @param array $exceptions
     * @return array
     */
    private function getRecursivePageChildren($pages, $id, $exceptions = [])
    {
        $return = [];

        if (count($pages) > 0) {
            $children = collect($pages)
                ->sortBy('friendly_url')
                ->reject(function ($page) use ($id) {
                    $found = collect($page->parents)->where('id', $id)->all();
                    return count($found) <= 0;
                })
                ->filter()
                ->all();

            if (count($children) > 0) {
                foreach ($children as $child) {
                    if ( ! in_array($child->friendly_url, $exceptions)) {
                        $child->children = $this->getRecursivePageChildren($pages, $child->id, $exceptions);
                        $return[] = $child;
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Return active global items
     *
     * @return mixed|GlobalItem[]
     */
    private function getActiveGlobalItems()
    {
        /** @var GlobalItem[] activeGlobalItems */
        if ( ! empty($this->activeGlobalItems) && ! is_null($this->activeGlobalItems)) return $this->activeGlobalItems;

        $this->activeGlobalItems = $this
            ->globalItems()
            ->isActive()
            ->with(
                [
                    'globalItemOptions' => function ($query) {
                        /** @var $query \Illuminate\Database\Eloquent\Builder */
                        $query->where('is_active', true);
                    }
                ],
                'categoryNames'
            )
            ->get();

        return $this->activeGlobalItems;
    }
}
