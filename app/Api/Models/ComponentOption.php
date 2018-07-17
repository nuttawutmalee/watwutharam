<?php

namespace App\Api\Models;

use App\Api\Constants\LogConstants;
use App\Api\Traits\IsActive;
use App\Api\Traits\IsRequired;
use App\Api\Traits\OptionValue;

/**
 * Class ComponentOption
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
 * @property bool $is_required
 *
 * @property bool $is_active
 *
 * @property string $component_id
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property Component $component
 *
 * @property OptionElementType $elementType
 *
 * @property SiteTranslation|SiteTranslation[] $siteTranslations
 *
 * @property ComponentOptionString $string
 *
 * @property ComponentOptionDate $date
 *
 * @property ComponentOptionDecimal $decimal
 *
 * @property ComponentOptionInteger $integer
 */
class ComponentOption extends BaseModel
{
    use OptionValue, IsActive, IsRequired;

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
        'is_required',
        'is_active',
        'component_id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_required' => 'boolean'
    ];

    /**
     * Auto Lazy-loading
     *
     * @var array
     */
    protected $with = [
        'component'
    ];

    /**
     * ComponentOption constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->siteRequired = false;
    }

    /**
     * Relationships
     */

    /**
     * Return a relationship with a component
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Component
     */
    public function component()
    {
        return $this->belongsTo('App\Api\Models\Component', 'component_id');
    }

    /**
     * Return a relationship with an element type
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne|OptionElementType
     */
    public function elementType()
    {
        return $this->morphOne('App\Api\Models\OptionElementType', 'item');
    }

    /**
     * Return a relationship with site translations
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany|SiteTranslation|SiteTranslation[]
     */
    public function siteTranslations()
    {
        return $this->morphMany('App\Api\Models\SiteTranslation', 'item');
    }

    /**
     * Option Values
     */

    /**
     * Return a relationship with a component option string
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|ComponentOptionString
     */
    public function string()
    {
        return $this->hasOne('App\Api\Models\ComponentOptionString', 'component_option_id');
    }

    /**
     * Return a relationship with a component option integer
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|ComponentOptionInteger
     */
    public function integer()
    {
        return $this->hasOne('App\Api\Models\ComponentOptionInteger', 'component_option_id');
    }

    /**
     * Return a relationship with a component option decimal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|ComponentOptionDecimal
     */
    public function decimal()
    {
        return $this->hasOne('App\Api\Models\ComponentOptionDecimal', 'component_option_id');
    }

    /**
     * Return a relationship with a component option date
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne|ComponentOptionDate
     */
    public function date()
    {
        return $this->hasOne('App\Api\Models\ComponentOptionDate', 'component_option_id');
    }

    /**
     * Custom Functions
     */

    /**
     * @param array $properties
     * @param null $action
     * @return CmsLog|mixed
     */
    public function getLatestChangesLog($properties = [], $action = null)
    {
        $latest = null;

        if (is_null($action)) $action = LogConstants::COMPONENT_OPTION_BEFORE_UPDATED;

        /** @var CmsLog[]|CmsLog|\Illuminate\Support\Collection $logs */
        $logs = CmsLog::where('action', $action)
            ->where('log_data', 'LIKE', '%"id":"' . $this->getKey() . '"%')
            ->orderBy('updated_at', 'DESC')
            ->get();

        if (empty($logs)) return $latest;

        if (empty($properties)) return $logs->first();

        /** @var CmsLog $log */
        foreach ($logs as $log) {
            $logData = json_decode($log->log_data);
            foreach ($properties as $property) {
                $logProperty = isset($logData->{$property}) ? $logData->{$property} : null;
                $currentProperty = isset($this->{$property}) ? $this->{$property} : null;

                if ( ! is_null($logProperty) && ! is_null($currentProperty)) {
                    if ($logProperty !== $currentProperty) {
                        return $log;
                    }
                }
            }
        }

        return $latest;
    }


    /**
     * Overrides
     */

    /**
     * Override delete function
     *
     * @param bool $cascadeOptionValue
     * @param bool $cascadeOptionElementType
     * @param bool $cascadeOptionSiteTranslation
     * @param bool $cascadeOptionUploadedFile
     * @return bool|null
     */
    public function delete($cascadeOptionValue = true, $cascadeOptionElementType = true, $cascadeOptionSiteTranslation = true, $cascadeOptionUploadedFile = false)
    {
        if ($cascadeOptionValue) {
            $this->deleteOptionValue();
        }

        if ($cascadeOptionElementType) {
            $this->deleteOptionElementType();
        }

        if ($cascadeOptionSiteTranslation) {
            $this->deleteOptionSiteTranslation();
        }

        if ($cascadeOptionUploadedFile) {
            $this->deleteOptionUploadedFile();
        }

        return parent::delete();
    }

    /**
     * Return a parent site or false if no sites associated
     *
     * @return bool
     */
    public function getParentSite()
    {
        return false;
    }
}
