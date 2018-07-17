<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GlobalItemOptionController extends BaseController
{
    /**
     * GlobalItemOptionController constructor.
     */
    function __construct()
    {
        $this->idName = (new GlobalItemOption)->getKeyName();
    }

    /**
     * Return all global item options
     *
     * @param Request $request
     * @return mixed
     */
    public function getGlobalItemOptions(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @var GlobalItemOption[]\\Illuminate\Support\Collection $options */
        $options = GlobalItemOption::all();

        $options->transform(function ($option) {
            /** @var GlobalItemOption $option */
            return $option->withNecessaryData();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($options->all());
    }

    /**
     * Return a global item option by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getGlobalItemOptionById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var GlobalItemOption $option */
        $option = GlobalItemOption::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($option->withNecessaryData()->all());
    }

    /**
     * Store a new global item option
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'global_item_id' => 'required|string|exists:global_items,id',
            'name' => 'required|string',
            'variable_name' => 'required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'option_type' => 'required|string|is_option_type',
            'option_value' => 'sometimes|nullable',
            'description' => 'sometimes|nullable|string',
            'is_required' => 'sometimes|is_boolean',
            'is_active' => 'sometimes|is_boolean',
            'is_visible' => 'sometimes|is_boolean',
            'element_type' => 'required|string|is_element_type|required_with:element_value',
            'element_value' => 'sometimes|nullable',
            'language_code' => 'sometimes|string|exists:languages,code|exists:site_languages',
            'translated_text' => 'sometimes|nullable|string',
            'file' => 'sometimes|file'
        ]);

        $this->authorizeForUser($this->auth->user(), 'create', GlobalItemOption::class);

        /** @var GlobalItem $globalItem */
        $globalItem = GlobalItem::findOrFail($request->input('global_item_id'));

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
        DB::transaction(function () use (&$created, $globalItem, $params, $request) {
            /** @var GlobalItemOption $globalItemOption */
            $globalItemOption = $globalItem->globalItemOptions()->create($params);

            $optionType = $request->input('option_type');
            $optionValue = $request->input('option_value');

            $elementType = $request->input('element_type');
            $elementValue = $request->input('element_value', null);

            $globalItemOption->upsertOptionElementType($elementType, $elementValue);

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $globalItemOption->upsertOptionUploadFile($file, $optionValue);
            } else {
                $globalItemOption->upsertOptionValue($optionType, $optionValue);

                if ($request->exists('language_code')) {
                    $globalItemOption->upsertOptionSiteTranslation($request->input('language_code'), $request->input('translated_text', null));
                }
            }

            $created = $globalItemOption->withNecessaryData()->all();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple global item options
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
            'data.*.variable_name' => 'sometimes|required|string|regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
            'data.*.name' => 'sometimes|required|string',
            'data.*.global_item_id' => 'sometimes|string|exists:global_items,id',
            'data.*.description' => 'sometimes|nullable|string',
            'data.*.is_required' => 'sometimes|is_boolean',
            'data.*.is_active' => 'sometimes|is_boolean',
            'data.*.is_visible' => 'sometimes|is_boolean',
            'data.*.element_type' => 'sometimes|required|string|is_element_type|required_with:data.*.element_value',
            'data.*.element_value' => 'sometimes|nullable',
            'data.*.language_code' => 'sometimes|string|exists:languages,code|exists:site_languages,language_code',
            'data.*.translated_text' => 'sometimes|nullable|string'
        ];
        $data['data.*.' . $this->idName] = 'required|string|distinct|exists:global_item_options,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedGlobalItemOptions = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var GlobalItemOption $option */
                $option = GlobalItemOption::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $option);

                $this->guardAgainstInvalidateRequest($value, [
                    'variable_name' => [
                        'sometimes',
                        'required',
                        'string',
                        'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                        Rule::unique('global_item_options', 'variable_name')
                            ->where('global_item_id', $option->global_item_id)
                            ->ignore($option->{$this->idName}, $this->idName)
                    ]
                ]);

                if (array_key_exists('global_item_id', $value)) {
                    /** @var GlobalItem $globalItem */
                    $globalItem = GlobalItem::findOrFail($value['global_item_id']);
                    $option->globalItem()->associate($globalItem);
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
            /** @var GlobalItemOption[]|\Illuminate\Support\Collection $options */
            $options = GlobalItemOption::whereIn($this->idName, $ids)->get();

            foreach ($options as $option) {
                array_push($updatedGlobalItemOptions, $option->withNecessaryData()->all());
            }
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedGlobalItemOptions);
    }

    /**
     * Update a global item option by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id) {
        /** @var GlobalItemOption $option */
        $option = GlobalItemOption::findOrFail($id);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.option_type' => 'sometimes|string|is_option_type',
            'data.option_value' => 'sometimes|nullable',
            'data.variable_name' => [
                'sometimes',
                'required',
                'string',
                'regex:' . ValidationRuleConstants::VARIABLE_NAME_REGEX,
                Rule::unique('global_item_options', 'variable_name')
                    ->where('global_item_id', $option->global_item_id)
                    ->ignore($option->{$this->idName}, $this->idName)
            ],
            'data.name' => 'sometimes|required|string',
            'data.global_item_id' => 'sometimes|string|exists:global_items,id',
            'data.description' => 'sometimes|nullable|string',
            'data.is_required' => 'sometimes|is_boolean',
            'data.is_active' => 'sometimes|is_boolean',
            'data.is_visible' => 'sometimes|is_boolean',
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
                if (array_key_exists('global_item_id', $data)) {
                    /** @var GlobalItem $globalItem */
                    $globalItem = GlobalItem::findOrFail($data['global_item_id']);
                    $option->globalItem()->associate($globalItem);
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
                if (array_key_exists('global_item_id', $data)) {
                    /** @var GlobalItem $globalItem */
                    $globalItem = GlobalItem::findOrFail($data['global_item_id']);
                    $option->globalItem()->associate($globalItem);
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

        /** @var GlobalItemOption $updated */
        $updated = $option->fresh();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated->withNecessaryData()->all());
    }

    /**
     * Delete multiple global item options
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:global_item_options,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var GlobalItemOption $option */
                $option = GlobalItemOption::findOrFail($value[$this->idName]);
                $this->authorizeForUser($this->auth->user(), 'delete', $option);
                $option->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a global item option by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var GlobalItemOption $option */
        $option = GlobalItemOption::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $option);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($option) {
            $option->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }
}
