<?php

namespace App\Api\Traits;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Models\CmsLog;
use App\Api\Models\Component;
use App\Api\Models\ComponentOption;
use App\Api\Models\ComponentOptionDate;
use App\Api\Models\ComponentOptionDecimal;
use App\Api\Models\ComponentOptionInteger;
use App\Api\Models\ComponentOptionString;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\GlobalItemOptionDate;
use App\Api\Models\GlobalItemOptionDecimal;
use App\Api\Models\GlobalItemOptionInteger;
use App\Api\Models\GlobalItemOptionString;
use App\Api\Models\OptionElementType;
use App\Api\Models\PageItemOption;
use App\Api\Models\PageItemOptionDate;
use App\Api\Models\PageItemOptionDecimal;
use App\Api\Models\PageItemOptionInteger;
use App\Api\Models\PageItemOptionString;
use App\Api\Models\TemplateItemOption;
use App\Api\Models\TemplateItemOptionDate;
use App\Api\Models\TemplateItemOptionDecimal;
use App\Api\Models\TemplateItemOptionInteger;
use App\Api\Models\TemplateItemOptionString;
use Illuminate\Support\Facades\DB;

/**
 * Class InheritComponentOptions
 *
 * @package App\Api\Traits
 *
 * @traitUses \Illuminate\Database\Eloquent\Model
 *
 * @property Component $component
 */
trait InheritComponentOptions
{
    /**
     * Item option relationship name
     *
     * @var null
     */
    protected $optionRelationship = null;

    /**
     * Component relationship name
     *
     * @var string
     */
    protected $componentRelationship = 'component';

    /**
     * Inherit this object with component options for its parent component
     *
     * @param null $component
     * @return \Illuminate\Support\Collection|null
     */
    public function inheritComponentOptions($component = null)
    {
        $this->guardAgainstInvalidRelationship();

        $inheritOptions = collect([]);
        $inheritComponent = $this->getInheritComponent($component);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($inheritComponent, &$inheritOptions) {
            /** @var \Illuminate\Support\Collection|ComponentOption[] $componentOptions */
            if ($componentOptions = $inheritComponent->componentOptions()->orderBy('created_at')->get()) {
                if ( ! empty($componentOptions)) {
                    $componentOptions->each(function ($option) use (&$inheritOptions) {

                        /** @var ComponentOption $option */
                        $optionCollection = collect($option);

                        $optionCreatedAt = $option->created_at;

                        $params = $optionCollection->only([
                            'name',
                            'variable_name',
                            'description',
                            'is_required',
                            'is_active',
                        ])->toArray();

                        /** @var GlobalItemOption|TemplateItemOption|PageItemOption $inheritOption */
                        if ($inheritOption = $this->{$this->optionRelationship}()->create($params)) {
                            try {
                                if ($optionValue = $option->getOptionValue()) {
                                    $type = $optionValue->option_type;
                                    $value = get_default_option_null_value($optionValue->option_value, $type);

                                    $inheritOption->upsertOptionValue($type, $value);
                                }
                            } catch (\Exception $e) {
                                throw new \Exception($e->getMessage(), $e->getCode());
                            }

                            try {
                                if ($elementType = $option->getOptionElementType()) {
                                    $type = $elementType->element_type;
                                    $value = $elementType->element_value;

                                    $inheritOption->upsertOptionElementType($type, $value);
                                }
                            } catch (\Exception $e) {
                                throw new \Exception($e->getMessage(), $e->getCode());
                            }

                            try {
                                if ($siteTranslations = $option->getOptionSiteTranslation()) {
                                    $siteTranslations->each(function ($translation) use ($inheritOption) {
                                        $inheritOption->upsertOptionSiteTranslation($translation->language_code, $translation->translated_text);
                                    });
                                }
                            } catch (\Exception $e) {
                                throw new \Exception($e->getMessage(), $e->getCode());
                            }

                            // Update created date
                            $inheritOption->created_at = $optionCreatedAt;

                            $inheritOption->save();

                            $newlyCreatedInheritOption = $inheritOption->fresh();

                            $inheritOptions->push($newlyCreatedInheritOption);
                        }
                    });
                }
            }
        });

        return $inheritOptions;
    }

    /**
     * Update inherit item options
     *
     * @param null $component
     * @return \Illuminate\Support\Collection
     */
    public function updateInheritComponentOptions($component = null)
    {
        $this->guardAgainstInvalidRelationship();

        $inheritOptions = collect([]);
        /** @var Component $inheritComponent */
        $inheritComponent = $this->getInheritComponent($component);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($inheritComponent, &$inheritOptions) {
            /** @var \Illuminate\Support\Collection|ComponentOption[] $componentOptions */
            if ($componentOptions = $inheritComponent->componentOptions()->orderBy('created_at')->get()) {
                if ( ! empty($componentOptions)) {
                    $componentOptions->each(function ($option) use ($inheritComponent, &$inheritOptions) {
                        /** @var ComponentOption $option */
                        $componentOptionVariableName = $option->variable_name;

                        /** @var GlobalItemOption|TemplateItemOption|PageItemOption|null $found */
                        $found = $this->{$this->optionRelationship}()
                            ->where('variable_name', $componentOptionVariableName)
                            ->first();

                        if (is_null($found)) {
                            /** @var CmsLog $latestChangesLog */
                            if ($latestChangesLog = $option->getLatestChangesLog(['variable_name'])) {
                                $latestComponent = json_decode($latestChangesLog->log_data);

                                if ($variableName = isset($latestComponent->variable_name) ? $latestComponent->variable_name : null) {
                                    /** @var GlobalItemOption|TemplateItemOption|PageItemOption|null $found */
                                    $found = $this->{$this->optionRelationship}()
                                        ->where('variable_name', $variableName)
                                        ->first();
                                }
                            }
                        }

                        if (is_null($found)) {
                            /**
                             * New item option
                             */

                            /** @var ComponentOption $option */
                            $optionCollection = collect($option);

                            $optionCreatedAt = $option->created_at;

                            $params = $optionCollection->only([
                                'name',
                                'variable_name',
                                'description',
                                'is_required',
                                'is_active',
                            ])->toArray();

                            /** @var GlobalItemOption|TemplateItemOption|PageItemOption $inheritOption */
                            if ($inheritOption = $this->{$this->optionRelationship}()->create($params)) {
                                try {
                                    if ($optionValue = $option->getOptionValue()) {
                                        $type = $optionValue->option_type;
                                        $value = get_default_option_null_value($optionValue->option_value, $type);

                                        $inheritOption->upsertOptionValue($type, $value);
                                    }
                                } catch (\Exception $e) {
                                    throw new \Exception($e->getMessage(), $e->getCode());
                                }

                                try {
                                    if ($elementType = $option->getOptionElementType()) {
                                        $type = $elementType->element_type;
                                        $value = $elementType->element_value;

                                        $inheritOption->upsertOptionElementType($type, $value);
                                    }
                                } catch (\Exception $e) {
                                    throw new \Exception($e->getMessage(), $e->getCode());
                                }

                                try {
                                    if ($siteTranslations = $option->getOptionSiteTranslation()) {
                                        $siteTranslations->each(function ($translation) use ($inheritOption) {
                                            $inheritOption->upsertOptionSiteTranslation($translation->language_code, $translation->translated_text);
                                        });
                                    }
                                } catch (\Exception $e) {
                                    throw new \Exception($e->getMessage(), $e->getCode());
                                }
                            }

                            // Update created date
                            $inheritOption->created_at = $optionCreatedAt;

                            $inheritOption->save();

                            $newlyCreatedInheritOption = $inheritOption->fresh();

                            $inheritOptions->push($newlyCreatedInheritOption);
                        } else {
                            /**
                             * Update item option
                             */

                            /**
                             * Name
                             */

                            if ($found->name !== $option->name) $found->name = $option->name;

                            /**
                             * Variable name
                             */

                            if ($found->variable_name !== $option->variable_name) $found->variable_name = $option->variable_name;

                            /**
                             * Option type
                             */

                            /** @var null|ComponentOptionString|ComponentOptionDate|ComponentOptionDecimal|ComponentOptionInteger|TemplateItemOptionString|TemplateItemOptionDate|TemplateItemOptionDecimal|TemplateItemOptionInteger|PageItemOptionString|PageItemOptionDate|PageItemOptionDecimal|PageItemOptionInteger|GlobalItemOptionString|GlobalItemOptionDate|GlobalItemOptionDecimal|GlobalItemOptionInteger $currentOptionValue **/
                            $currentOptionValue = $found->getOptionValue();

                            /** @var null|ComponentOptionString|ComponentOptionDate|ComponentOptionDecimal|ComponentOptionInteger|TemplateItemOptionString|TemplateItemOptionDate|TemplateItemOptionDecimal|TemplateItemOptionInteger|PageItemOptionString|PageItemOptionDate|PageItemOptionDecimal|PageItemOptionInteger|GlobalItemOptionString|GlobalItemOptionDate|GlobalItemOptionDecimal|GlobalItemOptionInteger $componentOptionValue **/
                            $componentOptionValue = $option->getOptionValue();

                            if ( ! is_null($currentOptionValue) && ! is_null($componentOptionValue)) {
                                if ($currentOptionValue->option_type !== $componentOptionValue->option_type) {
                                    try {
                                        $type = $componentOptionValue->option_type;
                                        $value = get_default_option_null_value($componentOptionValue->option_value, $type);
                                        $found->upsertOptionValue($type, $value);
                                        $found->deleteOptionSiteTranslation();
                                    } catch (\Exception $e) {
                                        throw new \Exception($e->getMessage(), $e->getCode());
                                    }
                                }
                            }

                            /**
                             * Element type/value
                             */

                            /** @var OptionElementType $currentElementTypeValue */
                            $currentElementTypeValue = $found->getOptionElementType();

                            /** @var OptionElementType $componentElementTypeValue */
                            $componentElementTypeValue = $option->getOptionElementType();

                            if ( ! is_null($currentElementTypeValue) && ! is_null($componentElementTypeValue)) {
                                if ($currentElementTypeValue->element_type !== $componentElementTypeValue->element_type) {
                                    try {
                                        $type = $componentOptionValue->option_type;
                                        $value = get_default_option_null_value($componentOptionValue->option_value, $type);
                                        $found->upsertOptionValue($type, $value);
                                        $found->upsertOptionElementType(
                                            $componentElementTypeValue->element_type,
                                            $componentElementTypeValue->element_value
                                        );
                                        $found->deleteOptionSiteTranslation();
                                    } catch (\Exception $e) {
                                        throw new \Exception($e->getMessage(), $e->getCode());
                                    }
                                } else if ($currentElementTypeValue->element_value !== $componentElementTypeValue->element_value) {
                                    $found->upsertOptionElementType(
                                        $componentElementTypeValue->element_type,
                                        $componentElementTypeValue->element_value
                                    );
                                }
                            }

                            $found->save();

                            $updated = $found->fresh();

                            $inheritOptions->push($updated);
                        }
                    });
                }
            }
        });

        return $inheritOptions;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    private function guardAgainstInvalidRelationship()
    {
        /** @var \App\Api\Models\GlobalItem|\App\Api\Models\TemplateItem|\App\Api\Models\PageItem $this */
        if ( ! method_exists($this, $this->optionRelationship) ||
            ! method_exists($this, $this->componentRelationship)) throw new \Exception(ErrorMessageConstants::RELATIONSHIP_NOT_FOUND);

        if (is_null($this->optionRelationship) ||
            is_null($this->componentRelationship) ||
            empty($this->optionRelationship) ||
            empty($this->componentRelationship)) throw new \Exception(ErrorMessageConstants::RELATIONSHIP_NOT_FOUND);

        return true;
    }

    /**
     * @param Component|null $component
     * @return Component|null
     * @throws \Exception
     */
    private function getInheritComponent(Component $component = null)
    {
        if (is_null($component)) {
            /** @var \App\Api\Models\Component $existedComponent */
            if ($existedComponent = $this->component) {
                $inheritComponent = $existedComponent;
            } else {
                return null;
            }
        } else {
            $inheritComponent = $component;
        }

        if ( ! is_null($component)) {
            if ( ! $component instanceof Component) throw new \Exception(ErrorMessageConstants::WRONG_MODEL);
        }

        return $inheritComponent;
    }
}