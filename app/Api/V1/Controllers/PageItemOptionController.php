<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\PageItem;
use App\Api\Models\PageItemOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PageItemOptionController extends BaseController
{
    /**
     * PageItemOptionController constructor.
     */
    function __construct()
    {
        $this->idName = (new PageItemOption)->getKeyName();
    }

    /**
     * Return all page item options
     *
     * @param Request $request
     * @return mixed
     */
    public function getPageItemOptions(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        $options = PageItemOption::all();
        $options->transform(function ($option) {
            /** @noinspection PhpUndefinedMethodInspection */
            return $option->withNecessaryData();
        });
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($options->all());
    }

    /**
     * Return a page item option by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getPageItemOptionById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        $option = PageItemOption::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($option->withNecessaryData()->all());
    }

    /**
     * Store a new page item option
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'page_item_id' => 'required|string|exists:page_items,id',
            'name' => 'required|string',
            'variable_name' => 'required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'option_type' => 'required|string|is_option_type',
            'option_value' => 'sometimes|nullable',
            'is_required' => 'sometimes|is_boolean',
            'is_active' => 'sometimes|is_boolean',
            'is_visible' => 'sometimes|is_boolean',
            'description' => 'sometimes|nullable|string',
            'element_type' => 'required|string|is_element_type|required_with:element_value',
            'element_value' => 'sometimes|nullable',
            'language_code' => 'sometimes|string|exists:languages,code|exists:site_languages',
            'translated_text' => 'sometimes|nullable|string',
            'file' => 'sometimes|file'
        ]);

        $this->authorizeForUser($this->auth->user(), 'create', PageItemOption::class);

        $pageItem = PageItem::findOrFail($request->input('page_item_id'));

        $params = $request->only(['name', 'variable_name']);

        if ($request->exists('description')) {
            $params['description'] = $request->input('description');
        }

        if ($request->exists('is_active')) {
            $params['is_active'] = $request->input('is_active');
        }

        if ($request->exists('is_required')) {
            $params['is_required'] = $request->input('is_required');
        }

        if ($request->exists('is_visible')) {
            $params['is_visible'] = $request->input('is_visible');
        }

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $pageItem, $params, $request) {
            /** @noinspection PhpUndefinedMethodInspection */
            $pageItemOption = $pageItem->pageItemOptions()->create($params);

            $optionType = $request->input('option_type');
            $optionValue = $request->input('option_value');

            $elementType = $request->input('element_type');
            $elementValue = $request->input('element_value', null);

            /** @noinspection PhpUndefinedMethodInspection */
            $pageItemOption->upsertOptionElementType($elementType, $elementValue);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                /** @noinspection PhpUndefinedMethodInspection */
                $pageItemOption->upsertOptionUploadFile($file, $optionValue);
            } else {
                /** @noinspection PhpUndefinedMethodInspection */
                $pageItemOption->upsertOptionValue($optionType, $optionValue);

                if ($request->exists('language_code')) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $pageItemOption->upsertOptionSiteTranslation($request->input('language_code'), $request->input('translated_text', null));
                }
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $created = $pageItemOption->withNecessaryData()->all();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple page item options
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $rules = [
            'data' => 'required|array',
            'data.*.option_type' => 'sometimes|string|is_option_type',
            'data.*.option_value' => 'sometimes|nullable',
            'data.*.is_required' => 'sometimes|is_boolean',
            'data.*.is_active' => 'sometimes|is_boolean',
            'data.*.is_visible' => 'sometimes|is_boolean',
            'data.*.variable_name' => 'sometimes|required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'data.*.name' => 'sometimes|required|string',
            'data.*.page_item_id' => 'sometimes|string|exists:page_items,id',
            'data.*.description' => 'sometimes|nullable|string',
            'data.*.element_type' => 'sometimes|required|string|is_element_type|required_with:data.*.element_value',
            'data.*.element_value' => 'sometimes|nullable',
            'data.*.language_code' => 'sometimes|string|exists:languages,code|exists:site_languages,language_code',
            'data.*.translated_text' => 'sometimes|nullable|string'
        ];
        $data['data.*.' . $this->idName] = 'required|string|distinct|exists:page_item_options,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedPageItemOptions = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var PageItemOption $option */
                $option = PageItemOption::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $option);

                $this->guardAgainstInvalidateRequest($value, [
                    'variable_name' => [
                        'sometimes',
                        'required',
                        'string',
                        'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                        Rule::unique('page_item_options', 'variable_name')
                            ->where('page_item_id', $option->page_item_id)
                            ->ignore($option->{$this->idName}, $this->idName)
                    ]
                ]);

                if (array_key_exists('page_item_id', $value)) {
                    $pageItem = PageItem::findOrFail($value['page_item_id']);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $option->pageItem()->associate($pageItem);
                }

                if (array_key_exists('option_type', $value) && array_key_exists('option_value', $value)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $option->upsertOptionValue($value['option_type'], $value['option_value']);
                }

                if (array_key_exists('element_type', $value)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $option->upsertOptionElementType($value['element_type'], $value['element_value'] ?: null);
                }

                if (array_key_exists('language_code', $value)) {
                    if (array_key_exists('translated_text', $value)) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $option->upsertOptionSiteTranslation($value['language_code'], $value['translated_text']);
                    }
                }

                $params = collect($value)->except([
                    $this->idName,
                    'option_type',
                    'option_value',
                    'element_type',
                    'element_value',
                    'language_code',
                    'translated_text'
                ])->toArray();

                $option->update($params);

                /** @noinspection PhpUndefinedMethodInspection */
                array_push($ids, $option->getKey());
            }
        });

        if ( ! empty($ids)) {
            $options = PageItemOption::whereIn($this->idName, $ids)->get();

            foreach ($options as $option) {
                array_push($updatedPageItemOptions, $option->withNecessaryData()->all());
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedPageItemOptions);
    }

    /**
     * Update a page item option by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id) {
        /** @var PageItemOption $option */
        $option = PageItemOption::findOrFail($id);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.option_type' => 'sometimes|string|is_option_type',
            'data.option_value' => 'sometimes|nullable',
            'data.is_required' => 'sometimes|is_boolean',
            'data.is_active' => 'sometimes|is_boolean',
            'data.is_visible' => 'sometimes|is_boolean',
            'data.variable_name' => [
                'sometimes',
                'required',
                'string',
                'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                Rule::unique('page_item_options', 'variable_name')
                    ->where('page_item_id', $option->page_item_id)
                    ->ignore($option->{$this->idName}, $this->idName)
            ],
            'data.name' => 'sometimes|required|string',
            'data.page_item_id' => 'sometimes|string|exists:page_items,id',
            'data.description' => 'sometimes|nullable|string',
            'data.element_type' => 'sometimes|required|string|is_element_type|required_with:data.element_value',
            'data.element_value' => 'sometimes|nullable',
            'data.language_code' => 'sometimes|string|exists:languages,code|exists:site_languages,language_code',
            'data.translated_text' => 'sometimes|nullable|string',
            'file' => 'sometimes|file'
        ]);

        $this->authorizeForUser($this->auth->user(), 'update', $option);

        $data = $request->input('data');

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            /** @noinspection PhpUndefinedMethodInspection */
            DB::transaction(function () use ($option, $data, $file) {
                if (array_key_exists('page_item_id', $data)) {
                    $pageItem = PageItem::findOrFail($data['page_item_id']);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $option->pageItem()->associate($pageItem);
                }

                if (array_key_exists('option_type', $data) && array_key_exists('option_value', $data)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $option->upsertOptionValue($data['option_type'], $data['option_value']);
                }

                if (array_key_exists('element_type', $data)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $option->upsertOptionElementType($data['element_type'], $data['element_value'] ?: null);
                }

                /** @noinspection PhpUndefinedMethodInspection */
                $option->upsertOptionUploadFile(
                    $file,
                    array_key_exists('option_value', $data) ? $data['option_value'] : null
                );

                $params = collect($data)->except([
                    $this->idName,
                    'option_type',
                    'option_value',
                    'element_type',
                    'element_value',
                    'language_code',
                    'translated_text'
                ])->toArray();

                $option->update($params);
            });
        } else {
            /** @noinspection PhpUndefinedMethodInspection */
            DB::transaction(function () use ($option, $data) {
                if (array_key_exists('page_item_id', $data)) {
                    $pageItem = PageItem::findOrFail($data['page_item_id']);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $option->pageItem()->associate($pageItem);
                }

                if (array_key_exists('option_type', $data) && array_key_exists('option_value', $data)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $option->upsertOptionValue($data['option_type'], $data['option_value']);
                }

                if (array_key_exists('element_type', $data)) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $option->upsertOptionElementType($data['element_type'], $data['element_value'] ?: null);
                }

                if (array_key_exists('language_code', $data)) {
                    if (array_key_exists('translated_text', $data)) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $option->upsertOptionSiteTranslation($data['language_code'], $data['translated_text']);
                    }
                }

                $params = collect($data)->except([
                    $this->idName,
                    'option_type',
                    'option_value',
                    'element_type',
                    'element_value',
                    'language_code',
                    'translated_text'
                ])->toArray();

                $option->update($params);
            });
        }

        /** @noinspection PhpUndefinedMethodInspection */
        $updated = $option->fresh();

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated->withNecessaryData()->all());
    }

    /**
     * Delete multiple page item options
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:page_item_options,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                $option = PageItemOption::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'delete', $option);

                $option->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a page item option by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        $option = PageItemOption::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $option);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($option) {
            $option->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }
}
