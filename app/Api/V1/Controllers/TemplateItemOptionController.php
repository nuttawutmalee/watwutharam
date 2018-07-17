<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\TemplateItem;
use App\Api\Models\TemplateItemOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TemplateItemOptionController extends BaseController
{
    /**
     * TemplateItemOptionController constructor.
     */
    function __construct()
    {
        $this->idName = (new TemplateItemOption)->getKeyName();
    }

    /**
     * Return all template item options
     *
     * @param Request $request
     * @return mixed
     */
    public function getTemplateItemOptions(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $options */
        $options = TemplateItemOption::all();

        $options->transform(function ($option) {
            /** @var TemplateItemOption $option */
            return $option->withNecessaryData();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($options->all());
    }

    /**
     * Return a template item option by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getTemplateItemOptionById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var TemplateItemOption $option */
        $option = TemplateItemOption::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($option->withNecessaryData()->all());
    }

    /**
     * Store a new template item option
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'template_item_id' => 'required|string|exists:template_items,id',
            'name' => 'required|string',
            'variable_name' => 'required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'option_type' => 'sometimes|nullable|string|is_option_type',
            'option_value' => 'sometimes|nullable',
            'description' => 'sometimes|nullable|string',
            'is_required' => 'sometimes|is_boolean',
            'is_active' => 'sometimes|is_boolean',
            'element_type' => 'required|string|is_element_type|required_with:element_value',
            'element_value' => 'sometimes|nullable',
            'language_code' => 'sometimes|required_with:translated_text|string|exists:languages,code|exists:site_languages',
            'translated_text' => 'sometimes|required_with:language_code|string',
            'file' => 'sometimes|file'
        ]);

        $this->authorizeForUser($this->auth->user(), 'create', TemplateItemOption::class);

        /** @var TemplateItem $templateItem */
        $templateItem = TemplateItem::findOrFail($request->input('template_item_id'));

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

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $templateItem, $params, $request) {
            /** @var TemplateItemOption $templateItemOption */
            $templateItemOption = $templateItem->templateItemOptions()->create($params);

            $optionType = $request->input('option_type', null);
            $optionValue = $request->input('option_value', null);

            $elementType = $request->input('element_type');
            $elementValue = $request->input('element_value', null);

            $templateItemOption->upsertOptionElementType($elementType, $elementValue);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $templateItemOption->upsertOptionUploadFile($file, $optionValue);
            } else {
                if ($value = $templateItemOption->upsertOptionValue($optionType, $optionValue)) {
                    if ($request->exists('language_code')) {
                        $templateItemOption->upsertOptionSiteTranslation($request->input('language_code'), $request->input('translated_text', null));
                    }
                }
            }

            $created = $templateItemOption->withNecessaryData()->all();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple template item options
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $rules = [
            'data' => 'required|array',
            'data.*.option_type' => 'sometimes|nullable|string|is_option_type',
            'data.*.option_value' => 'sometimes|nullable',
            'data.*.variable_name' => 'sometimes|required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'data.*.name' => 'sometimes|required|string',
            'data.*.template_item_id' => 'sometimes|string|exists:template_items,id',
            'data.*.description' => 'sometimes|nullable|string',
            'data.*.is_required' => 'sometimes|is_boolean',
            'data.*.is_active' => 'sometimes|is_boolean',
            'data.*.element_type' => 'sometimes|required|string|is_element_type|required_with:data.*.element_value',
            'data.*.element_value' => 'sometimes|nullable',
            'data.*.language_code' => 'sometimes|string|exists:languages,code|exists:site_languages,language_code',
            'data.*.translated_text' => 'sometimes|nullable|string'
        ];
        $data['data.*.' . $this->idName] = 'required|string|distinct|exists:template_item_options,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedTemplateItemOptions = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var TemplateItemOption $option */
                $option = TemplateItemOption::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $option);

                $this->guardAgainstInvalidateRequest($value, [
                    'variable_name' => [
                        'sometimes',
                        'required',
                        'string',
                        'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                        Rule::unique('template_item_options', 'variable_name')
                            ->where('template_item_id', $option->template_item_id)
                            ->ignore($option->{$this->idName}, $this->idName)
                    ]
                ]);

                if (array_key_exists('template_item_id', $value)) {
                    /** @var TemplateItem $templateItem */
                    $templateItem = TemplateItem::findOrFail($value['template_item_id']);
                    $option->templateItem()->associate($templateItem);
                }

                if (array_key_exists('option_type', $value) && array_key_exists('option_value', $value)) {
                    $option->upsertOptionValue($value['option_type'], $value['option_value']);
                }

                if (array_key_exists('element_type', $value)) {
                    $option->upsertOptionElementType($value['element_type'], $value['element_value'] ?: null);
                }

                if (array_key_exists('language_code', $value)) {
                    if (array_key_exists('translated_text', $value)) {
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

                array_push($ids, $option->getKey());
            }
        });

        if ( ! empty($ids)) {
            /** @var TemplateItemOption[]|\Illuminate\Support\Collection $options */
            $options = TemplateItemOption::whereIn($this->idName, $ids)->get();

            foreach ($options as $option) {
                array_push($updatedTemplateItemOptions, $option->withNecessaryData()->all());
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedTemplateItemOptions);
    }

    /**
     * Update a template item option by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id) {
        /** @var TemplateItemOption $option */
        $option = TemplateItemOption::findOrFail($id);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.option_type' => 'sometimes|nullable|string|is_option_type',
            'data.option_value' => 'sometimes|nullable',
            'data.variable_name' => [
                'sometimes',
                'required',
                'string',
                'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                Rule::unique('template_item_options', 'variable_name')
                    ->where('template_item_id', $option->template_item_id)
                    ->ignore($option->{$this->idName}, $this->idName)
            ],
            'data.name' => 'sometimes|required|string',
            'data.template_item_id' => 'sometimes|string|exists:template_items,id',
            'data.description' => 'sometimes|nullable|string',
            'data.is_required' => 'sometimes|is_boolean',
            'data.is_active' => 'sometimes|is_boolean',
            'data.element_type' => 'sometimes|required_with:data.element_value|string|is_element_type',
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
                if (array_key_exists('template_item_id', $data)) {
                    /** @var TemplateItem $templateItem */
                    $templateItem = TemplateItem::findOrFail($data['template_item_id']);
                    $option->templateItem()->associate($templateItem);
                }

                if (array_key_exists('option_type', $data) && array_key_exists('option_value', $data)) {
                    $option->upsertOptionValue($data['option_type'], $data['option_value']);
                }

                if (array_key_exists('element_type', $data)) {
                    $option->upsertOptionElementType($data['element_type'], $data['element_value'] ?: null);
                }

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
                if (array_key_exists('template_item_id', $data)) {
                    /** @var TemplateItem $templateItem */
                    $templateItem = TemplateItem::findOrFail($data['template_item_id']);
                    $option->templateItem()->associate($templateItem);
                }

                if (array_key_exists('option_type', $data) && array_key_exists('option_value', $data)) {
                    $option->upsertOptionValue($data['option_type'], $data['option_value']);
                }

                if (array_key_exists('element_type', $data)) {
                    $option->upsertOptionElementType($data['element_type'], $data['element_value'] ?: null);
                }

                if (array_key_exists('language_code', $data)) {
                    if (array_key_exists('translated_text', $data)) {
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

        /** @var TemplateItemOption $updated */
        $updated = $option->fresh();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated->withNecessaryData()->all());
    }

    /**
     * Delete multiple template item options
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:template_item_options,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var TemplateItemOption $option */
                $option = TemplateItemOption::findOrFail($value[$this->idName]);
                $this->authorizeForUser($this->auth->user(), 'delete', $option);
                $option->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a template item option by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var TemplateItemOption $option */
        $option = TemplateItemOption::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $option);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($option) {
            $option->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }
}
