<?php

namespace App\Api\Traits;

use App\Api\Models\CategoryName;

/**
 * Class Category
 *
 * @package App\Api\Traits
 *
 * @traitUses \Illuminate\Database\Eloquent\Model
 */
trait Category
{
    /**
     * Category name relationship
     *
     * @var string
     */
    protected $categoryRelationship = 'categoryNames';

    /**
     * Insert or update option category names
     *
     * @param array $names
     * @return null
     */
    public function upsertOptionCategoryNames($names = [])
    {
        $categories = [];

        if (isset($this->categoryRelationship) && ! empty($this->categoryRelationship)) {
            if (method_exists($this, $this->categoryRelationship)) {
                if (empty($names)) {
                    $this->detachOptionCategoryNames();
                    return $categories;
                }

                $ids = [];

                if ( ! is_array($names)) $names = array($names);

                foreach ($names as $name) {

                    if (is_array($name)) {
                        $value = array_key_exists('name', $name) ? $name['name'] : $name;
                    } else {
                        $value = $name;
                    }

                    $value = preg_replace('/[\s-]+/', '_', $value);
                    $value = preg_replace('/_{2,}/', '_', $value);

                    /** @var CategoryName $category */
                    $category = CategoryName::firstOrCreate([
                        'name' => strtoupper($value)
                    ]);

                    $ids[] = $category->getKey();
                    $categories[] = $category;
                }

                $this->{$this->categoryRelationship}()->sync($ids);
            }
        }

        return $categories;
    }

    /**
     * Detach Option Category
     */
    public function detachOptionCategoryNames()
    {
        if (isset($this->categoryRelationship) && ! empty($this->categoryRelationship)) {
            if (method_exists($this, $this->categoryRelationship)) {
                $this->{$this->categoryRelationship}()->detach();
            }
        }
    }

    /**
     * Return category names
     *
     * @param null $name
     * @return mixed
     */
    public function getOptionCategoryName($name = null)
    {
        if (isset($this->categoryRelationship) && ! empty($this->categoryRelationship)) {
            if (method_exists($this, $this->categoryRelationship)) {
                if (is_null($name)) {
                    return $this->{$this->categoryRelationship};
                } else {
                    return $this->{$this->categoryRelationship}()->where('name', strtoupper($name))->first();
                }
            }
        }

        return null;
    }

    /**
     * Return this object with category names
     *
     * @return array
     */
    public function withOptionCategoryNames()
    {
        /** @var CategoryName|CategoryName[]|\Illuminate\Support\Collection $categories */
        if ($categories = $this->getOptionCategoryName()) {
            $names = $categories->pluck('name')->all();
            return array_merge($this->toArray(), [
                'categories' => $names
            ]);
        } else {
            return array_merge($this->toArray(), [
                'categories' => []
            ]);
        }
    }

    /**
     * Return this object with necessary data
     *
     * @param array $fresh
     * @param bool $withCategory
     * @return \Illuminate\Support\Collection
     */
    public function withNecessaryData($fresh = [], $withCategory = true)
    {
        $collection = collect($this->fresh($fresh));

        if ($withCategory) {
            $collection = $collection->union(collect($this->withOptionCategoryNames()));
        }

        return $collection;
    }
}