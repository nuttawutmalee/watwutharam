<?php

namespace App\Api\Models;

/**
 * Class SiteTranslation
 *
 * @package App\Api\Models
 *
 * @property string $id
 *
 * @property string $item_id
 *
 * @property string $item_type
 *
 * @property string $language_code
 *
 * @property string $translated_text
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property ComponentOptionString|TemplateItemOptionString|PageItemOptionString|GlobalItemOptionString $item
 *
 * @property SiteLanguage $siteLanguage
 *
 * @property Language $language
 */
class SiteTranslation extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'item_id',
        'item_type',
        'language_code',
        'translated_text'
    ];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = [
        'item'
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with a morphed item
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo|ComponentOptionString|TemplateItemOptionString|PageItemOptionString|GlobalItemOptionString
     */
    public function item()
    {
        return $this->morphTo();
    }

    /**
     * Return a relationship with a site language
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|SiteLanguage
     */
    public function siteLanguage()
    {
        return $this->belongsTo('App\Api\Models\SiteLanguage', 'language_code', 'language_code');
    }

    /**
     * Return a relationship with a language
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Language
     */
    public function language()
    {
        return $this->belongsTo('App\Api\Models\Language', 'language_code');
    }
}
