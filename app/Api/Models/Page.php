<?php

namespace App\Api\Models;

use App\Api\Constants\HelperConstants;
use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Tools\Query\QueryFactory;
use App\Api\Traits\Category;
use App\Api\Traits\IsActive;

/**
 * Class Page
 *
 * @package App\Api\Models
 *
 * @property string $id
 *
 * @property string $name
 *
 * @property string $variable_name
 *
 * @property string $description
 *
 * @property string $friendly_url
 *
 * @property string $permissions
 *
 * @property bool $is_active
 *
 * @property string $template_id
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property \Datetime $published_at
 *
 * @property Template $template
 *
 * @property PageItem|PageItem[] $pageItems
 *
 * @property CategoryName|CategoryName[] $categoryNames
 *
 * @property Page|Page[] $parents
 *
 * @property Page|Page[] $children
 */
class Page extends BaseModel
{
    use IsActive, Category;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'variable_name',
        'description',
        'friendly_url',
        'permissions',
        'is_active',
        'template_id',
        'published_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $with = [
        'template'
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with a template
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Template
     */
    public function template()
    {
        return $this->belongsTo('App\Api\Models\Template', 'template_id');
    }

    /**
     * Return a relationship with page items
     *
     * @return mixed|\Illuminate\Database\Eloquent\Relations\HasMany|PageItem|PageItem[]
     */
    public function pageItems()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasMany('App\Api\Models\PageItem', 'page_id')->orderBy('display_order');
    }

    /**
     * Return a relationship with category names
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|CategoryName|CategoryName[]
     */
    public function categoryNames()
    {
        return $this
            ->belongsToMany('App\Api\Models\CategoryName', 'category_name_item_mappings', 'item_id', 'category_name_id')
            ->withTimestamps();
    }

    /**
     * Return a relationship with pages as parents
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|Page|Page[]
     */
    public function parents()
    {
        return $this
            ->belongsToMany('App\Api\Models\Page', 'parent_page_mappings', 'page_id', 'parent_id')
            ->withTimestamps();
    }

    /**
     * Return a relationship with pages as children
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|Page|Page[]
     */
    public function children()
    {
        return $this
            ->belongsToMany('App\Api\Models\Page', 'parent_page_mappings', 'parent_id', 'page_id')
            ->withTimestamps();
    }

    /**
     * Overrides
     */

    /**
     * Override delete function
     *
     * @param bool $cascadePageItem
     * @param bool $cascadeParentMappings
     * @param bool $cascadeCategoryNameMappings
     * @return bool|null
     */
    public function delete($cascadePageItem = true, $cascadeParentMappings = true, $cascadeCategoryNameMappings = true)
    {
        if ($cascadePageItem) {
            if (count($this->pageItems) > 0) {
                foreach ($this->pageItems as $pageItem) {
                    $pageItem->delete();
                }
            }
        }

        if ($cascadeParentMappings) {
            $this->children()->detach();
            $this->parents()->detach();
        }

        if ($cascadeCategoryNameMappings) {
            $this->detachOptionCategoryNames();
        }

        return parent::delete();
    }

    /**
     * Custom Functions
     */

    /**
     * Return active page item data
     *
     * @param Site $site
     * @param null $languageCode
     * @param null $previewData
     * @param array $filterItems
     * @param array $pages
     * @return array
     */
    public function getPageItemData($site, $languageCode = null, $previewData = null, $filterItems = [], $pages = [])
    {
        /** @var $pageQuery \Illuminate\Database\Eloquent\Builder */
        if (empty($filterItems)) {
            $pageQuery = $this->pageItems();
        } else {
            $pageQuery = $this
                ->pageItems()
                ->whereIn('variable_name', $filterItems);
        }

        /** @var PageItem[] $pageItems */
        $pageItems = $pageQuery
            ->isActive()
            ->with(
                [
                    'pageItemOptions' => function ($query) {
                        /** @var $query \Illuminate\Database\Eloquent\Builder */
                        $query->where('is_active', true);
                    }
                ]
            )
            ->get();

        $data = [
            HelperConstants::HELPER_PAGE_DATA => []
        ];

        if ( ! empty($pageItems)) {
            /** @var PageItem $pageItem */
            foreach ($pageItems as $pageItem) {
                $optionData = [];

                /**
                 * @var PageItem|GlobalItem $item
                 * @var PageItemOption[]|PageItemOption|GlobalItemOption[]|GlobalItemOption $itemOptions
                 */
                if ($globalItem = $pageItem->globalItem) {
                    if ( ! $globalItem->is_active) continue;
                    $item = $globalItem;
                    $itemOptions = $globalItem->globalItemOptions;
                } else {
                    $item = $pageItem;
                    $itemOptions = $pageItem->pageItemOptions;
                }

                if ( ! empty($itemOptions)) {
                    /** @var PageItemOption|GlobalItemOption $itemOption */
                    foreach ($itemOptions as $itemOption) {
                        $return = [];
                        $queryJSON = null;

                        $optionArray = $itemOption->withNecessaryData([], $languageCode)->all();

                        if ($optionArray['element_type'] === OptionElementTypeConstants::CONTROL_LIST) {
                            $controlListData = array_key_exists('translated_text', $optionArray)
                                ? json_recursive_decode($optionArray['translated_text'])
                                : json_recursive_decode($optionArray['option_value']);

                            $previewValue = extract_control_list_data($site, $controlListData, $itemOption->getKey(), $languageCode, null, $pages);

                            if ( ! empty($previewData) && ! is_null($previewData)) {
                                try {
                                    $previewValue = isset($previewData->{$item->variable_name}->{$optionArray['variable_name']})
                                        ? extract_control_list_data($site, $previewData->{$item->variable_name}->{$optionArray['variable_name']}, $itemOption->getKey(), $languageCode, null, $pages)
                                        : $previewValue;
                                } catch (\Exception $e) {}
                            }

                            if ($query = QueryFactory::make(
                                $itemOption->getKey(),
                                $previewValue,
                                $optionArray['element_type'],
                                $optionArray['element_value'],
                                $site,
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
                                    $previewValue = isset($previewData->{$item->variable_name}->{$optionArray['variable_name']})
                                        ? $previewData->{$item->variable_name}->{$optionArray['variable_name']}
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

                $displayOrder = $pageItem->display_order;
                if ( ! empty($previewData) && ! is_null($previewData)) {
                    try {
                        $displayOrder = isset($previewData->{$item->variable_name}->{HelperConstants::HELPER_DISPLAY_ORDER})
                            ? $previewData->{$item->variable_name}->{HelperConstants::HELPER_DISPLAY_ORDER}
                            : $pageItem->display_order;
                    } catch (\Exception $e) {}
                }

                $optionData[HelperConstants::HELPER_ID] = $item->id;
                $optionData[HelperConstants::HELPER_DISPLAY_ORDER] = $displayOrder;
                $optionData[HelperConstants::HELPER_VARIABLE_NAME] = $item->variable_name;
                $optionData[HelperConstants::HELPER_CREATED_AT] = $item->created_at;
                $optionData[HelperConstants::HELPER_UPDATED_AT] = $item->updated_at;
                $optionData[HelperConstants::HELPER_TEMPLATE] = (is_null($item->component))
                    ? $item->variable_name
                    : $item->component->variable_name;
                $data[HelperConstants::HELPER_PAGE_DATA][$item->variable_name] = $optionData;
            }
        }

        return $data;
    }

    /**
     * Inherit this object with template and its children
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function inheritTemplate()
    {
        if ( ! method_exists($this, 'template')) return null;

        if (is_null($this->template) || empty($this->template())) return null;

        $pageItems = collect([]);

        /** @var Template $inheritTemplate */
        $inheritTemplate = $this->template;

        /** @var TemplateItem[]|\Illuminate\Support\Collection $templateItems */
        if ($templateItems = $inheritTemplate->templateItems) {
            if ( ! empty($templateItems)) {
                $templateItems->each(function ($templateItem) use ($pageItems) {
                    /** @var TemplateItem $templateItem */
                    $templateItemCollection = collect($templateItem);

                    $params = $templateItemCollection->only([
                        'name',
                        'variable_name',
                        'description',
                        'component_id',
                        'display_order'
                    ])->toArray();

                    /** @var PageItem $pageItem */
                    if ($pageItem = $this->pageItems()->create($params)) {
                        /** @var \Illuminate\Support\Collection|TemplateItemOption[] $templateItemOptions */
                        if ($templateItemOptions = $templateItem->templateItemOptions()->orderBy('created_at')->get()) {
                            if ( ! empty($templateItemOptions)) {
                                $templateItemOptions->each(function ($option) use ($pageItem) {
                                    /**
                                     * @var TemplateItemOption $option
                                     * @var PageItemOption $pageItemOption
                                     */
                                    if ($pageItemOption = $pageItem->pageItemOptions()->where('variable_name', $option->variable_name)->first()) {
                                        try {
                                            if ($optionValue = $option->getOptionValue()) {
                                                $type = $optionValue->option_type;
                                                $value = get_default_option_null_value($optionValue->option_value, $type);

                                                $pageItemOption->upsertOptionValue($type, $value);
                                            }
                                        } catch (\Exception $e) {
                                            throw new \Exception($e->getMessage(), $e->getCode());
                                        }

                                        try {
                                            if ($elementType = $option->getOptionElementType()) {
                                                $type = $elementType->element_type;
                                                $value = $elementType->element_value;

                                                $pageItemOption->upsertOptionElementType($type, $value);
                                            }
                                        } catch (\Exception $e) {
                                            throw new \Exception($e->getMessage(), $e->getCode());
                                        }

                                        try {
                                            if ($siteTranslations = $option->getOptionSiteTranslation()) {
                                                $siteTranslations->each(function ($translation) use ($pageItemOption) {
                                                    $pageItemOption->upsertOptionSiteTranslation($translation->language_code, $translation->translated_text);
                                                });
                                            }
                                        } catch (\Exception $e) {
                                            throw new \Exception($e->getMessage(), $e->getCode());
                                        }
                                    }
                                });

                                $pageItems->push($pageItem);
                            }
                        }
                    }
                });
            }
        }

        return $pageItems;
    }
}
