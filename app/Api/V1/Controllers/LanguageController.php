<?php

namespace App\Api\V1\Controllers;

use App\Api\Models\Language;
use /** @noinspection PhpUnusedAliasInspection */
    App\Api\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LanguageController extends BaseController
{
    /**
     * LanguageController constructor.
     */
    function __construct()
    {
        $this->idName = (new Language)->getKeyName();
    }

    /**
     * Return all languages
     *
     * @param Request $request
     * @return mixed
     */
    public function getLanguages(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(Language::all());
    }

    /**
     * Return a language by its code
     *
     * @param Request $request
     * @param $code
     * @return mixed
     */
    public function getLanguageByCode(/** @noinspection PhpUnusedParameterInspection */ Request $request, $code)
    {
        $code = strtolower($code);

        /** @var Language $language */
        $language = Language::findOrFail($code);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($language);
    }

    /**
     * Store a new language
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'code' => 'required|string|unique:languages',
            'name' => 'required|string|unique:languages',
            'hreflang' => 'sometimes|nullable|string',
            'locale' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|is_boolean'
        ]);

        $this->authorizeForUser($this->auth->user(), 'create', Language::class);

        $params = $request->only(['name', 'code']);
        $params['code'] = strtolower($params['code']);

        if ($request->exists('is_active')) {
            $params['is_active'] = $request->input('is_active');
        }

        if ($request->exists('hreflang')) {
            $params['hreflang'] = $request->input('hreflang');
        }

        if ($request->exists('locale')) {
            $params['locale'] = $request->input('locale');
        }

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $params) {
            /** @var Language $language */
            $language = Language::create($params);
            $created = $language->fresh();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple languages
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $rules = [
            'data' => 'required|array',
            'data.*.is_active' => 'sometimes|is_boolean'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:languages,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedLanguages = [];
        $codes = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$codes) {
            foreach ($data as $key => $value) {
                /** @var Language $language */
                $language = Language::findOrFail(strtolower($value[$this->idName]));

                $this->authorizeForUser($this->auth->user(), 'update', $language);

                $this->guardAgainstInvalidateRequest($value, [
                    'name' => [
                        'sometimes',
                        'required',
                        'string',
                        Rule::unique('languages', 'name')->ignore($language->{$this->idName}, $this->idName)
                    ],
                    'code' => [
                        'sometimes',
                        'required',
                        'string',
                        Rule::unique('languages', 'code')->ignore($language->{$this->idName}, $this->idName),
                    ],
                    'hreflang' => 'sometimes|nullable|string',
                    'locale' => 'sometimes|nullable|string'
                ]);

                $value = collect($value)->except($this->idName)->toArray();

                $language->update($value);

                array_push($codes, strtolower($language->getKey()));
            }
        });

        if ( ! empty($codes)) {
            /** @var Language[]|\Illuminate\Support\Collection  $updatedLanguages */
            $updatedLanguages = Language::whereIn($this->idName, $codes)->get();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedLanguages);
    }

    /**
     * Update a language by its code
     *
     * @param Request $request
     * @param $code
     * @return mixed
     */
    public function updateByCode(Request $request, $code)
    {
        $code = strtolower($code);

        /** @var Language $language */
        $language = Language::findOrFail($code);

        $this->authorizeForUser($this->auth->user(), 'update', $language);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.is_active' => 'sometimes|is_boolean',
            'data.name' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('languages', 'name')->ignore($language->{$this->idName}, $this->idName)
            ],
            'data.code' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('languages', 'code')->ignore($language->{$this->idName}, $this->idName),
            ],
            'data.hreflang' => 'sometimes|nullable|string',
            'data.locale' => 'sometimes|nullable|string'
        ]);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, $language) {
            $data = collect($data)->except($this->idName)->toArray();
            $language->update($data);
        });

        /** @var Language $updated */
        $updated = $language->fresh();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Delete multiple languages
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array',
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:languages,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var Language $language */
                $language = Language::findOrFail(strtolower($value[$this->idName]));
                $this->authorizeForUser($this->auth->user(), 'delete', $language);
                $language->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a language by its code
     *
     * @param Request $request
     * @param $code
     * @return mixed
     */
    public function deleteByCode(/** @noinspection PhpUnusedParameterInspection */ Request $request, $code)
    {
        /** @var Language $language */
        $language = Language::findOrFail(strtolower($code));

        $this->authorizeForUser($this->auth->user(), 'delete', $language);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($language) {
            $language->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }
}
