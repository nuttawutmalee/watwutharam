<?php

namespace App\Api\Traits;


use App\Api\Constants\ErrorMessageConstants;
use App\Api\Constants\HelperConstants;
use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\ComponentOptionDate;
use App\Api\Models\ComponentOptionDecimal;
use App\Api\Models\ComponentOptionInteger;
use App\Api\Models\ComponentOptionString;
use App\Api\Models\GlobalItemOptionDate;
use App\Api\Models\GlobalItemOptionDecimal;
use App\Api\Models\GlobalItemOptionInteger;
use App\Api\Models\GlobalItemOptionString;
use /** @noinspection PhpUnusedAliasInspection */
    App\Api\Models\Language;
use App\Api\Models\OptionElementType;
use App\Api\Models\PageItemOptionDate;
use App\Api\Models\PageItemOptionDecimal;
use App\Api\Models\PageItemOptionInteger;
use App\Api\Models\PageItemOptionString;
use App\Api\Models\Site;
use App\Api\Models\SiteTranslation;
use App\Api\Models\TemplateItemOptionDate;
use App\Api\Models\TemplateItemOptionDecimal;
use App\Api\Models\TemplateItemOptionInteger;
use App\Api\Models\TemplateItemOptionString;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Bkwld\Croppa\Facade as Croppa;

/**
 * Class OptionValue
 *
 * @package App\Api\Traits
 *
 * @traitUses \Illuminate\Database\Eloquent\Model
 */
trait OptionValue
{
    /**
     * List of ignore types
     *
     * @var array
     */
    protected $ignoreOptionTypes = [];

    /**
     * LIst of type relationships
     *
     * @var array
     */
    protected $optionTypeRelationship = [
        'string' => OptionValueConstants::STRING_RELATIONSHIP,
        'integer' => OptionValueConstants::INTEGER_RELATIONSHIP,
        'decimal' => OptionValueConstants::DECIMAL_RELATIONSHIP,
        'date' => OptionValueConstants::DATE_RELATIONSHIP
    ];

    /**
     * Site translations relationship name
     *
     * @var string
     */
    protected $siteTranslationRelationship = 'siteTranslations';

    /**
     * Element type relationship name
     *
     * @var string
     */
    protected $elementTypeRelationship = 'elementType';

    /**
     * Is site required or not?
     *
     * @var bool
     */
    public $siteRequired = true;

    /**
     * Abstract function for a getParentSite function
     *
     * @return mixed|Site|boolean
     */
    abstract public function getParentSite();

    /**
     * Option Value
     */

    /**
     * Insert or update an option value
     *
     * @param string $type
     * @param $value
     * @param bool $isTouched
     * @return \Illuminate\Database\Eloquent\Model|ComponentOptionString|ComponentOptionDate|ComponentOptionDecimal|ComponentOptionInteger|TemplateItemOptionString|TemplateItemOptionDate|TemplateItemOptionDecimal|TemplateItemOptionInteger|PageItemOptionString|PageItemOptionDate|PageItemOptionDecimal|PageItemOptionInteger|GlobalItemOptionString|GlobalItemOptionDate|GlobalItemOptionDecimal|GlobalItemOptionInteger
     */
    public function upsertOptionValue($type = OptionValueConstants::STRING, $value = null, $isTouched = true)
    {
        $value = $this->getProperValueByType($type, $value);

        if ($this->hasOptionValueAlreadyExisted()) {
            $this->truncateOptionValue();
            $optionValue = $this->createOptionValue($type, $value);
        } else {
            $optionValue = $this->createOptionValue($type, $value);
        }

        if ($isTouched) {
            $this->touch();
        }

        return $optionValue;
    }

    /**
     * Create a new option value
     *
     * @param $type
     * @param $value
     * @return null|\Illuminate\Database\Eloquent\Model|ComponentOptionString|ComponentOptionDate|ComponentOptionDecimal|ComponentOptionInteger|TemplateItemOptionString|TemplateItemOptionDate|TemplateItemOptionDecimal|TemplateItemOptionInteger|PageItemOptionString|PageItemOptionDate|PageItemOptionDecimal|PageItemOptionInteger|GlobalItemOptionString|GlobalItemOptionDate|GlobalItemOptionDecimal|GlobalItemOptionInteger
     * @throws \Exception
     */
    private function createOptionValue($type, $value)
    {
        $optionValue = null;
        switch (strtoupper($type)) {
            case null:
                break;
            case OptionValueConstants::STRING:
                if (array_key_exists(OptionValueConstants::STRING_RELATIONSHIP, $this->optionTypeRelationship)) {
                    if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP], $this->ignoreOptionTypes)) {
                        if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP])) {
                            
                            /** @var ComponentOptionDate|TemplateItemOptionDate|PageItemOptionDate|GlobalItemOptionDate $optionValue */
                            $optionValue = $this
                                ->{$this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP]}()
                                ->create(['option_value' => $value]);
                                
                        } else {
                            throw new \Exception('Option type\'s relationship not found', 500);
                        }
                    }
                } else {
                    throw new \Exception('Option type\'s key for relationship array not found', 500);
                }
                break;
            case OptionValueConstants::DATE:
                if (array_key_exists(OptionValueConstants::DATE_RELATIONSHIP, $this->optionTypeRelationship)) {
                    if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::DATE_RELATIONSHIP], $this->ignoreOptionTypes)) {
                        if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::DATE_RELATIONSHIP])) {
                            
                            /** @var ComponentOptionDate|TemplateItemOptionDate|PageItemOptionDate|GlobalItemOptionDate $optionValue */
                            $optionValue = $this
                                ->{$this->optionTypeRelationship[OptionValueConstants::DATE_RELATIONSHIP]}()
                                ->create(['option_value' => $value]);
                                
                        } else {
                            throw new \Exception('Option type\'s relationship not found', 500);
                        }
                    }
                } else {
                    throw new \Exception('Option type\'s key for relationship array not found', 500);
                }
                break;
            case OptionValueConstants::INTEGER:
                if (array_key_exists(OptionValueConstants::INTEGER_RELATIONSHIP, $this->optionTypeRelationship)) {
                    if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::INTEGER_RELATIONSHIP], $this->ignoreOptionTypes)) {
                        if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::INTEGER_RELATIONSHIP])) {

                            /** @var ComponentOptionInteger|TemplateItemOptionInteger|PageItemOptionInteger|GlobalItemOptionInteger $optionValue */
                            $optionValue = $this
                                ->{$this->optionTypeRelationship[OptionValueConstants::INTEGER_RELATIONSHIP]}()
                                ->create(['option_value' => $value]);
                                
                        } else {
                            throw new \Exception('Option type\'s relationship not found', 500);
                        }
                    }
                } else {
                    throw new \Exception('Option type\'s key for relationship array not found', 500);
                }
                break;
            case OptionValueConstants::DECIMAL:
                if (array_key_exists(OptionValueConstants::DECIMAL_RELATIONSHIP, $this->optionTypeRelationship)) {
                    if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::DECIMAL_RELATIONSHIP], $this->ignoreOptionTypes)) {
                        if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::DECIMAL_RELATIONSHIP])) {

                            /** @var ComponentOptionDecimal|TemplateItemOptionDecimal|PageItemOptionDecimal|GlobalItemOptionDecimal $optionValue */
                            $optionValue = $this
                                ->{$this->optionTypeRelationship[OptionValueConstants::DECIMAL_RELATIONSHIP]}()
                                ->create(['option_value' => $value]);
                                
                        } else {
                            throw new \Exception('Option type\'s relationship not found', 500);
                        }
                    }
                } else {
                    throw new \Exception('Option type\'s key for relationship array not found', 500);
                }
                break;
            default:
                throw new ModelNotFoundException();
        }
        return $optionValue;
    }

    /**
     * Delete option value
     *
     * @param bool $isTouched
     */
    public function deleteOptionValue($isTouched = true)
    {
        $this->truncateOptionValue($isTouched);
    }

    /**
     * Delete all option value
     *
     * @param bool $isTouched
     */
    public function truncateOptionValue($isTouched = true)
    {
        if (array_key_exists(OptionValueConstants::STRING_RELATIONSHIP, $this->optionTypeRelationship)) {
            if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP], $this->ignoreOptionTypes)) {
                if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP])) {
                    $this->{$this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP]}()->delete();

                    if ($isTouched) {
                        $this->touch();
                    }
                }
            }
        }

        if (array_key_exists(OptionValueConstants::DATE_RELATIONSHIP, $this->optionTypeRelationship)) {
            if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::DATE_RELATIONSHIP], $this->ignoreOptionTypes)) {
                if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::DATE_RELATIONSHIP])) {
                    $this->{$this->optionTypeRelationship[OptionValueConstants::DATE_RELATIONSHIP]}()->delete();

                    if ($isTouched) {
                        $this->touch();
                    }
                }
            }
        }

        if (array_key_exists(OptionValueConstants::INTEGER_RELATIONSHIP, $this->optionTypeRelationship)) {
            if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::INTEGER_RELATIONSHIP], $this->ignoreOptionTypes)) {
                if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::INTEGER_RELATIONSHIP])) {
                    $this->{$this->optionTypeRelationship[OptionValueConstants::INTEGER_RELATIONSHIP]}()->delete();

                    if ($isTouched) {
                        $this->touch();
                    }
                }
            }
        }

        if (array_key_exists(OptionValueConstants::DECIMAL_RELATIONSHIP, $this->optionTypeRelationship)) {
            if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::DECIMAL_RELATIONSHIP], $this->ignoreOptionTypes)) {
                if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::DECIMAL_RELATIONSHIP])) {
                    $this->{$this->optionTypeRelationship[OptionValueConstants::DECIMAL_RELATIONSHIP]}()->delete();

                    if ($isTouched) {
                        $this->touch();
                    }
                }
            }
        }
    }

    /**
     * Return an option value
     *
     * @param null $type
     * @return null|ComponentOptionString|ComponentOptionDate|ComponentOptionDecimal|ComponentOptionInteger|TemplateItemOptionString|TemplateItemOptionDate|TemplateItemOptionDecimal|TemplateItemOptionInteger|PageItemOptionString|PageItemOptionDate|PageItemOptionDecimal|PageItemOptionInteger|GlobalItemOptionString|GlobalItemOptionDate|GlobalItemOptionDecimal|GlobalItemOptionInteger
     */
    public function getOptionValue($type = null)
    {
        $optionValue = null;

        if (is_null($type)) {
            if (array_key_exists(OptionValueConstants::STRING_RELATIONSHIP, $this->optionTypeRelationship)) {
                if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP], $this->ignoreOptionTypes)) {
                    if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP])) {
                        $optionValue = $this->{$this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP]}()->first();
                    }
                }
            }

            if (array_key_exists(OptionValueConstants::DATE_RELATIONSHIP, $this->optionTypeRelationship) && is_null($optionValue)) {
                if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::DATE_RELATIONSHIP], $this->ignoreOptionTypes)) {
                    if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::DATE_RELATIONSHIP])) {
                        $optionValue = $this->{$this->optionTypeRelationship[OptionValueConstants::DATE_RELATIONSHIP]}()->first();
                    }
                }
            }

            if (array_key_exists(OptionValueConstants::INTEGER_RELATIONSHIP, $this->optionTypeRelationship) && is_null($optionValue)) {
                if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::INTEGER_RELATIONSHIP], $this->ignoreOptionTypes)) {
                    if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::INTEGER_RELATIONSHIP])) {
                        $optionValue = $this->{$this->optionTypeRelationship[OptionValueConstants::INTEGER_RELATIONSHIP]}()->first();
                    }
                }
            }

            if (array_key_exists(OptionValueConstants::DECIMAL_RELATIONSHIP, $this->optionTypeRelationship) && is_null($optionValue)) {
                if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::DECIMAL_RELATIONSHIP], $this->ignoreOptionTypes)) {
                    if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::DECIMAL_RELATIONSHIP])) {
                        $optionValue = $this->{$this->optionTypeRelationship[OptionValueConstants::DECIMAL_RELATIONSHIP]}()->first();
                    }
                }
            }
        } else {
            if ($relationship = OptionValueConstants::getRelationshipByType($type)) {
                if (array_key_exists($relationship, $this->optionTypeRelationship) && is_null($optionValue)) {
                    if ( ! in_array($this->optionTypeRelationship[$relationship], $this->ignoreOptionTypes)) {
                        if (method_exists($this, $this->optionTypeRelationship[$relationship])) {
                            $optionValue = $this->{$this->optionTypeRelationship[$relationship]}()->first();
                        }
                    }
                }
            }
        }

        return $optionValue;
    }

    /**
     * Return this object with an option value
     *
     * @return array
     */
    public function withOptionValue()
    {
        if ($value = $this->getOptionValue()) {
            return array_merge($this->toArray(), [
                'option_type' => $value->option_type,
                'option_value' => $value->option_value
            ]);
        } else {
            return array_merge($this->toArray(), [
                'option_type' => null,
                'option_value' => null
            ]);
        }
    }

    /**
     * Return true if this object already has an option value
     *
     * @return bool
     */
    private function hasOptionValueAlreadyExisted()
    {
        return ! is_null($this->getOptionValue());
    }

    /**
     * Return true if this object's option value is the same as an input type
     *
     * @param $type
     * @return bool
     */
    /** @noinspection PhpUnusedPrivateMethodInspection */ private function hasOptionValueOfType(/** @noinspection PhpDocSignatureInspection */ $type)
    {
        if ($optionValue = $this->getOptionValue($type)) {
            return $optionValue->option_type === $type;
        }

        return false;
    }

    /**
     * Return a proper option value by an option type
     *
     * @param $type
     * @param $value
     * @return float|int|null|string
     * @throws \Exception
     */
    private function getProperValueByType($type, $value)
    {
        if (is_null($value)) return null;

        switch (strtoupper($type)) {
            case OptionValueConstants::STRING:
                $value = (string)$value;
                break;
            case OptionValueConstants::DECIMAL:
                $value = doubleval($value);
                break;
            case OptionValueConstants::INTEGER:
                if (is_boolean($value, false)) {
                    $value = intval(to_boolean($value, false));
                } else {
                    $value = intval($value);
                }
                break;
            case OptionValueConstants::DATE:
                try {
                    Carbon::parse($value);
                } catch (\Exception $e) {
                    throw new \Exception(ErrorMessageConstants::INVALID_DATE_STRING_FORMAT);
                }
                break;
            default:
                throw new \Exception(ErrorMessageConstants::INVALID_OPTION_TYPE);
        }
        return $value;
    }

    /**
     * Option Element Type
     */

    /**
     * Insert or update an element type
     *
     * @param string $type
     * @param null $value
     * @param bool $isTouched
     * @return mixed|null|OptionElementType
     */
    public function upsertOptionElementType($type = OptionElementTypeConstants::TEXTBOX, $value = null, $isTouched = true)
    {
        $elementType = null;

        if (isset($this->elementTypeRelationship) && ! empty($this->elementTypeRelationship)) {
            if (method_exists($this, $this->elementTypeRelationship)) {
                $elementType = $this->{$this->elementTypeRelationship}()->updateOrCreate(
                    [
                        'item_id' => $this->{$this->primaryKey},
                        'item_type' => class_basename($this)
                    ],
                    [
                        'element_type' => $type,
                        'element_value' => is_json($value) ? $value : json_encode($value)
                    ]
                );

                if ($isTouched) {
                    $this->touch();
                }
            }
        }

        return $elementType;
    }

    /**
     * Delete option element type
     *
     * @param $isTouched
     */
    public function deleteOptionElementType($isTouched = true)
    {
        $this->truncateOptionElementType($isTouched);
    }

    /**
     * Delete option element type
     *
     * @param bool $isTouched
     */
    public function truncateOptionElementType($isTouched = true)
    {
        if (isset($this->elementTypeRelationship) && ! empty($this->elementTypeRelationship)) {
            if (method_exists($this, $this->elementTypeRelationship)) {
                $this->{$this->elementTypeRelationship}()->delete();

                if ($isTouched) {
                    $this->touch();
                }
            }
        }
    }

    /**
     * Return an element type
     *
     * @return null|OptionElementType
     */
    public function getOptionElementType()
    {
        $elementType = null;

        if (isset($this->elementTypeRelationship) && ! empty($this->elementTypeRelationship)) {
            if (method_exists($this, $this->elementTypeRelationship)) {
                $elementType = $this->{$this->elementTypeRelationship}()->first();
            }
        }

        return $elementType;
    }

    /**
     * Return this object with an element type
     *
     * @return array
     */
    public function withOptionElementType()
    {
        if ($elementType = $this->getOptionElementType()) {
            return array_merge($this->toArray(), [
                'element_type' => $elementType->element_type,
                'element_value' => $elementType->element_value
            ]);
        } else {
            return array_merge($this->toArray(), [
                'element_type' => OptionElementTypeConstants::TEXTBOX,
                'element_value' => null
            ]);
        }
    }


    /**
     * Option Site Translation
     */

    /**
     * Insert or update a site translation
     *
     * @param $languageCode
     * @param null $translatedText
     * @param bool $isTouched
     * @return null|SiteTranslation
     */
    public function upsertOptionSiteTranslation($languageCode = null, $translatedText = null, $isTouched = true)
    {
        $siteTranslation = null;

        if (is_null($languageCode)) return null;

        if (isset($this->siteTranslationRelationship) && ! empty($this->siteTranslationRelationship)) {
            if (method_exists($this, $this->siteTranslationRelationship)) {
                $this->guardAgainstLanguageCodeThatDoesNotBelongToParentSite($languageCode);

                /** @var SiteTranslation $siteTranslation */
                $siteTranslation = $this->{$this->siteTranslationRelationship}()->updateOrCreate(
                    [
                        'item_id' => $this->{$this->primaryKey},
                        'item_type' => class_basename($this),
                        'language_code' => $languageCode
                    ],
                    [
                        'language_code' => $languageCode,
                        'translated_text' => $translatedText
                    ]
                );

                if ($isTouched) {
                    $this->touch();
                }
            }
        }

        return $siteTranslation;
    }

    /**
     * Delete option's site translation
     *
     * @param bool $isTouched
     */
    public function deleteOptionSiteTranslation($isTouched = true)
    {
        $this->truncateOptionSiteTranslation($isTouched);
    }

    /**
     * Truncate option's site translation
     *
     * @param bool $isTouched
     */
    public function truncateOptionSiteTranslation($isTouched = true)
    {
        if (isset($this->siteTranslationRelationship) && ! empty($this->siteTranslationRelationship)) {
            if (method_exists($this, $this->siteTranslationRelationship)) {
                $this->{$this->siteTranslationRelationship}()->delete();

                if ($isTouched) {
                    $this->touch();
                }
            }
        }
    }

    /**
     * Return a site translation
     *
     * @param null $languageCode
     * @return null|SiteTranslation|SiteTranslation[]|\Illuminate\Support\Collection
     */
    public function getOptionSiteTranslation($languageCode = null)
    {
        $siteTranslation = null;

        if (isset($this->siteTranslationRelationship) && ! empty($this->siteTranslationRelationship)) {
            if (method_exists($this, $this->siteTranslationRelationship)) {
                if (is_null($languageCode)) {

                    /** @var SiteTranslation[]|\Illuminate\Support\Collection $siteTranslation */
                    $siteTranslation = $this->{$this->siteTranslationRelationship}()->get();
                } else {

                    /** @var SiteTranslation $siteTranslation */
                    $siteTranslation = $this->{$this->siteTranslationRelationship}()
                        ->where('language_code', $languageCode)
                        ->first();
                }
            }
        }

        return $siteTranslation;
    }

    /**
     * Return this object with a site translation
     *
     * @param null $languageCode
     * @return array
     */
    public function withOptionSiteTranslation($languageCode = null)
    {
        $optionValue = $this->getOptionValue();

        if (is_null($languageCode)) {
            /** @var SiteTranslation $translation */
            $translation = $this->load($this->siteTranslationRelationship);

            return array_merge($translation->toArray(), [
                'translated_text' => $optionValue ? $optionValue->option_value : null
            ]);
        } else {
            if ($siteTranslation = $this->getOptionSiteTranslation($languageCode)) {
                return array_merge($this->toArray(), [
                    'translated_text' => $siteTranslation->translated_text,
                    'language_code' => $siteTranslation->language_code
                ]);
            } else {
                /** @var SiteTranslation $translation */
                $translation = $this->load($this->siteTranslationRelationship);

                return array_merge($translation->toArray(), [
                    'translated_text' => $optionValue ? $optionValue->option_value : null,
                    'language_code' => $languageCode
                ]);
            }
        }
    }

    /**
     * Upload File
     */

    /**
     * Insert or update an uploaded file
     *
     * @param $file
     * @param null $path
     * @param bool $isTouched
     * @return null
     * @throws \Exception
     */
    public function upsertOptionUploadFile($file, $path = null, $isTouched = true)
    {
        if ( ! $file instanceof UploadedFile && ! $file instanceof File) return null;

        if (is_null($path) && ! empty($path)) {
            throw new \Exception(ErrorMessageConstants::FILE_PATH_IS_REQUIRED);
        }

        $path = trim($path);

        $uploadsPath = trim_uploads_path(config('cms.' . get_cms_application() . '.uploads_path'));
        $path = empty($uploadsPath)
            ? preg_replace('/^\/|\\\/', '', trim_uploads_path($path))
            : $uploadsPath . '/' . preg_replace('/^\/|\\\/', '', trim_uploads_path($path));
        $path = preg_replace('/^\/|\\\/', '', $path);

        $this->guardAgainstInvalidFilePath($path);

        $this->guardAgainstReservedFilePath($path);

        $dirName = pathinfo($path, PATHINFO_DIRNAME);
        $baseName = pathinfo($path, PATHINFO_BASENAME);

        $toBeUploaded = preg_replace('/^\//', '', $dirName . '/' . $baseName);

        $toBeUploaded = str_slash_to_forward_slash($toBeUploaded);
        $toBeUploaded = trim_uploads_path($toBeUploaded);

        /** @noinspection PhpUndefinedMethodInspection */
        if (Storage::exists($toBeUploaded)) {
            if ($exist = $this->getOptionUploadedFile()) {
                if (sha1_is_same_file($file, public_path() . '/' . $exist)) return $exist;
            }

            $dirName = pathinfo($toBeUploaded, PATHINFO_DIRNAME);
            $filename = pathinfo($toBeUploaded, PATHINFO_FILENAME);
            $extension = pathinfo($toBeUploaded, PATHINFO_EXTENSION);

            if (preg_match('/(.*?)_(\d+)$/', $filename, $match)) {
                $base = $match[1];
                $number = intVal($match[2]);
            } else {
                $base = $filename;
                $number = 0;
            }

            /** @noinspection PhpUndefinedMethodInspection */
            do {
                $toBeUploaded = $dirName . '/' . $base . '_' . ++$number . '.' . $extension;
            } while (Storage::exists($toBeUploaded));
        }

        $dirName = pathinfo($toBeUploaded, PATHINFO_DIRNAME);
        $baseName = pathinfo($toBeUploaded, PATHINFO_BASENAME);

        if ($dirName === '.') {
            $dirName = '';
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $savedPath = Storage::putFileAs(
            $dirName, $file, $baseName
        );

        if (app()->environment('testing')) {
            $savedPath = HelperConstants::UPLOADS_FOLDER_TESTING . '/' . $savedPath;
        } else {
            $savedPath = HelperConstants::UPLOADS_FOLDER . '/' . $savedPath;
        }

        $this->upsertOptionValue(OptionValueConstants::STRING, $savedPath);

        if ($isTouched) {
            $this->touch();
        }

        return $savedPath;
    }

    /**
     * Delete all option's uploaded files
     *
     * @param bool $isTouched
     */
    public function deleteOptionUploadedFile($isTouched = true)
    {
        $this->truncateOptionUploadedFile($isTouched);
    }

    /**
     * Delete all option's uploaded files
     *
     * @param bool $isTouched
     */
    public function truncateOptionUploadedFile($isTouched = true)
    {
        if (array_key_exists(OptionValueConstants::STRING_RELATIONSHIP, $this->optionTypeRelationship)) {
            if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP], $this->ignoreOptionTypes)) {
                if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP])) {
                    if ($optionValue = $this->{$this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP]}) {
                        $path = trim_uploads_path($optionValue->option_value);

                        /** @noinspection PhpUndefinedMethodInspection */
                        if (Storage::exists($path)) {
                            /** @noinspection PhpUndefinedMethodInspection */
                            Croppa::delete($path);

                            if ($isTouched) {
                                $this->touch();
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Return an uploaded file
     *
     * @param null $filename
     * @return null
     */
    public function getOptionUploadedFile($filename = null)
    {
        $file = null;

        if (array_key_exists(OptionValueConstants::STRING_RELATIONSHIP, $this->optionTypeRelationship)) {
            if ( ! in_array($this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP], $this->ignoreOptionTypes)) {
                if (method_exists($this, $this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP])) {
                    if ($optionValue = $this->{$this->optionTypeRelationship[OptionValueConstants::STRING_RELATIONSHIP]}) {
                        $path = trim_uploads_path($optionValue->option_value);

                        /** @noinspection PhpUndefinedMethodInspection */
                        if (Storage::exists($path)) {
                            if (is_null($filename)) {
                                if (app()->environment('testing')) {
                                    return HelperConstants::UPLOADS_FOLDER_TESTING . '/' . $path;
                                } else {
                                    return HelperConstants::UPLOADS_FOLDER . '/' . $path;
                                }
                            } else {
                                if (empty(pathinfo($filename, PATHINFO_EXTENSION))) {
                                    if (strtolower(pathinfo($path, PATHINFO_FILENAME)) === strtolower(pathinfo($filename, PATHINFO_FILENAME))) {
                                        if (app()->environment('testing')) {
                                            return HelperConstants::UPLOADS_FOLDER_TESTING . '/' . $path;
                                        } else {
                                            return HelperConstants::UPLOADS_FOLDER . '/' . $path;
                                        }
                                    }
                                } else {
                                    if (strtolower(pathinfo($path, PATHINFO_BASENAME)) === strtolower(pathinfo($filename, PATHINFO_BASENAME))) {
                                        if (app()->environment('testing')) {
                                            return HelperConstants::UPLOADS_FOLDER_TESTING . '/' . $path;
                                        } else {
                                            return HelperConstants::UPLOADS_FOLDER . '/' . $path;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $file;
    }

    /**
     * Return true if this object's uploaded file has the same filename as an input filename
     *
     * @param null $filename
     * @return bool
     */
    public function hasOptionUploadedFile($filename = null)
    {
        if ($files = $this->getOptionUploadedFile($filename)) {
            return ! is_null($files) && ! empty($files);
        }

        return false;
    }

    /**
     * Customs
     */

    /**
     * Return this object with a necessary data
     *
     * @param array $fresh
     * @param null $languageCode
     * @param bool $withOptionValue
     * @param bool $withOptionElementType
     * @return \Illuminate\Support\Collection
     */
    public function withNecessaryData($fresh = [], $languageCode = null, $withOptionValue = true, $withOptionElementType = true)
    {
        $collection = collect($this->fresh($fresh));

        if (is_null($languageCode)) {
            if (isset($this->siteTranslationRelationship) && ! empty($this->siteTranslationRelationship)) {
                if (method_exists($this, $this->siteTranslationRelationship)) {
                    $collection = collect($this->fresh($this->siteTranslationRelationship));
                }
            }
        }

        if ($withOptionValue) {
            $collection = $collection->union(collect($this->withOptionValue()));
        }

        if ($withOptionElementType) {
            $collection = $collection->union(collect($this->withOptionElementType()));
        }

        $collection = $collection->union(collect($this->withOptionSiteTranslation($languageCode)));

        return $collection;
    }

    /**
     * Throw an exception if the language code does not belong to the parent site
     *
     * @param $languageCode
     * @return null
     * @throws \Exception
     */
    private function guardAgainstLanguageCodeThatDoesNotBelongToParentSite($languageCode)
    {
        if ($site = $this->getParentSite()) {
            if ($site->languages()->where('language_code', $languageCode)->count() <= 0) {
                throw new \Exception(ErrorMessageConstants::SITE_LANGUAGE_NOT_FOUND);
            }
        } else {
            if ($this->siteRequired) {
                throw new \Exception(ErrorMessageConstants::SITE_NOT_FOUND);
            }
        }

        return true;
    }

    /**
     * Throw an exception if the input file path is an invalid file path
     *
     * @param $path
     * @throws \Exception
     */
    private function guardAgainstInvalidFilePath($path)
    {
        if ( ! str_is_valid_path($path) || !! preg_match('/\/?\.+\//', $path)) {
            throw new \Exception(ErrorMessageConstants::FILE_PATH_INVALID);
        }
    }

    /**
     * Throw an exception if the file path is already reserved
     *
     * @param $path
     * @param array $reserved
     * @throws \Exception
     */
    private function guardAgainstReservedFilePath($path, $reserved = [])
    {
        if ( ! is_array($reserved)) {
            $reserved = (array) $reserved;
        }

        $reserved[] = config('cms.' . get_cms_application() . '.crops_path');

        $needle = join('|', $reserved);
        if (preg_match('/^(' . preg_quote($needle, '/') . ')/', $path)) {
            throw new \Exception(ErrorMessageConstants::FILE_PATH_IS_RESERVED);
        }
    }
}