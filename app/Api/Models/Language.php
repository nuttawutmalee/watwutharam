<?php

namespace App\Api\Models;

use App\Api\Traits\IsActive;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Rutorika\Sortable\BelongsToSortedManyTrait;

/** @noinspection PhpSignatureMismatchDuringInheritanceInspection */
/** @noinspection PhpHierarchyChecksInspection */

/**
 * Class Language
 *
 * @package App\Api\Models
 *
 * @property string $code
 *
 * @property string $name
 *
 * @property string $hreflang
 *
 * @property string $locale
 *
 * @property bool $is_active
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property Site|Site[] $sites
 *
 * @property SiteLanguage $pivot
 *
 * @property SiteTranslation|SiteTranslation[] $siteTranslations
 *
 * @method static Builder find($id, array $columns = ['*'])
 *
 * @method static Builder findOrFail($id, $columns = ['*'])
 *
 * @method static Builder firstOrCreate(array $attributes, array $values = array())
 *
 * @method static Builder create(array $attributes = [])
 *
 * @method static Builder where(string|array $column, string $operator = null, mixed $value = null, string $boolean = 'and')
 *
 * @method static Builder whereIn($column, $values, $boolean = 'and', $not = false)
 *
 * @method static Builder whereHas(string $relation, $callback = null, string $operator = '>=', int $count = 1)
 *
 * @method static Builder|Model with($relations)
 *
 * @method Model load($relations)
 *
 * @method array toArray()
 *
 * @method get()
 *
 * @method $this|Model|null first()
 *
 * @method $this|Model|null last()
 *
 * @method static Builder isMainLanguage()
 *
 * @method static Builder isNotMainLanguage()
 */
class Language extends Model
{
    use Notifiable, BelongsToSortedManyTrait, IsActive;

    /**
     * Primary key
     *
     * @var string
     */
    protected $primaryKey = 'code';

    /**
     * Disable auto-increment key
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'is_active',
        'hreflang',
        'locale'
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
     * Return a relationship with sites
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|Site|Site[]
     */
    public function sites()
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection */
        return $this
            ->belongsToSortedMany('App\Api\Models\Site', 'display_order', 'site_languages', 'language_code', 'site_id', 'sites')
            ->withPivot('display_order', 'is_active', 'is_main')
            ->withTimestamps();
    }

    /**
     * Return a relationship with site translations
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|SiteTranslation|SiteTranslation[]
     */
    public function siteTranslations()
    {
        return $this->hasMany('App\Api\Models\SiteTranslation', 'language_code');
    }

    /**
     * Overrides
     */

    /**
     * Override delete function
     *
     * @param bool $cascadeSiteIntermediateTable
     * @param bool $cascadeSiteTranslation
     * @return bool|null
     */
    public function delete($cascadeSiteIntermediateTable = true, $cascadeSiteTranslation = true)
    {
        if ($cascadeSiteIntermediateTable) {
            $this->sites()->detach();
        }

        if ($cascadeSiteTranslation) {
            $this->siteTranslations()->delete();
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
        return 'sites';
    }

    /**
     * Override new pivot function
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
     * Scopes
     */

    /**
     * Return an eloquent query scope to check whether is_main equals true
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function scopeIsMainLanguage($query)
    {
        return $query->whereHas('sites', function ($q) {
            /** @var $q \Illuminate\Database\Eloquent\Builder */
            $q->where('site_languages.is_main', true);
        });
    }

    /**
     * Return an eloquent query scope to check whether is_main equals false
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return mixed
     */
    public function scopeIsNotMainLanguage($query)
    {
        return $query->whereHas('sites', function ($q) {
            /** @var $q \Illuminate\Database\Eloquent\Builder */
            $q->where('site_languages.is_main', false);
        });
    }

    /**
     * Custom functions
     */

    /**
     * @param $siteId
     */
    public function cascadeDeleteTranslations($siteId)
    {
        /** @var \Illuminate\Support\Collection $translations */
        $translations = $this->siteTranslations;

        if (!empty($translations)) {
            $siteTranslations = $translations->filter(function ($translation) use ($siteId) {
                /** @var \App\Api\Models\ComponentOption|\App\Api\Models\GlobalItemOption|\App\Api\Models\TemplateItemOption|\App\Api\Models\PageItemOption $item */
                $item = $translation->item;

                if (!$item) return false;

                /** @var \App\Api\Models\Site $site */
                try {
                    $site = $item->getParentSite();
                } catch (\Exception $e) {
                    $site = false;
                }

                if ($site === false) return false;

                return $site->id === $siteId;
            })->all();

            if (!empty($siteTranslations)) {
                $ids = collect($siteTranslations)->pluck('id')->all();
                $this->siteTranslations()->whereIn('id', $ids)->delete();
            }
        }
    }
}
