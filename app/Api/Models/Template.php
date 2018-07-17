<?php

namespace App\Api\Models;

/**
 * Class Template
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
 * @property string $site_id
 *
 * @property \Datetime $created_at
 *
 * @property \Datetime $updated_at
 *
 * @property Site $site
 *
 * @property Page|Page[] $pages
 *
 * @property TemplateItem|TemplateItem[] $templateItems
 */
class Template extends BaseModel
{
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
        'site_id'
    ];

    /**
     * Eager loading relationships
     *
     * @var array
     */
    protected $with = [
        'site'
    ];

    /**
     * Relationships
     */

    /**
     * Return a relationship with a site
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo|Site
     */
    public function site()
    {
        return $this->belongsTo('App\Api\Models\Site', 'site_id');
    }

    /**
     * Return a relationship with pages
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany|Page|Page[]
     */
    public function pages()
    {
        return $this->hasMany('App\Api\Models\Page', 'template_id');
    }

    /**
     * Return a relationship with template items
     *
     * @return mixed|\Illuminate\Database\Eloquent\Relations\HasMany|TemplateItem|TemplateItem[]
     */
    public function templateItems()
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->hasMany('App\Api\Models\TemplateItem', 'template_id')->orderBy('display_order');
    }

    /**
     * Overrides
     */

    /**
     * Override delete function
     *
     * @param bool $cascadeTemplateItem
     * @param bool $cascadePage
     * @return bool|null
     */
    public function delete($cascadeTemplateItem = true, $cascadePage = true)
    {
        if ($cascadeTemplateItem) {
            if (count($this->templateItems) > 0) {
                foreach ($this->templateItems as $templateItem) {
                    $templateItem->delete();
                }
            }
        }

        if ($cascadePage) {
            if (count($this->pages) > 0) {
                foreach ($this->pages as $page) {
                    $page->delete();
                }
            }
        }

        return parent::delete();
    }
}
