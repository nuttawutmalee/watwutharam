<?php

namespace App\Api\V1\Controllers;

use App\Api\Models\RedirectUrl;
use App\Api\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response as BaseResponse;

class RedirectUrlController extends BaseController
{
    /**
     * RedirectUrlController constructor.
     */
    function __construct()
    {
        $this->idName = (new RedirectUrl)->getKeyName();
    }

    /**
     * Return all redirect urls
     *
     * @param Request $request
     * @return mixed
     */
    public function getRedirectUrls(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(RedirectUrl::all());
    }

    /**
     * Return a redirect url by its id or its source url
     *
     * @param Request $request
     * @param $idOrUrl
     * @return mixed
     */
    public function getRedirectUrlByIdOrSourceUrl(/** @noinspection PhpUnusedParameterInspection */ Request $request, $idOrUrl)
    {
        try {
            /** @var RedirectUrl $redirectUrl */
            $redirectUrl = RedirectUrl::findOrFail($idOrUrl);
        } catch (\Exception $e) {
            /** @var RedirectUrl $redirectUrl */
            $redirectUrl = RedirectUrl::where('source_url', $idOrUrl)->firstOrFail();
        }
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($redirectUrl);
    }

    /**
     * Store a new redirect url
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'site_id' => 'required|string|exists:sites,id',
            'source_url' => 'required|string',
            'destination_url' => 'required|string',
            'is_active' => 'sometimes|is_boolean',
            'status_code' => 'sometimes|nullable|numeric|between:301,399'
        ]);

        $this->authorizeForUser($this->auth->user(), 'create', RedirectUrl::class);

        $params = $request->only(['source_url', 'destination_url']);

        if ($request->exists('is_active')) {
            $params['is_active'] = $request->input('is_active');
        }
        if ($request->exists('status_code')) {
            $statusCode = intval($request->input('status_code'));
            $params['status_code'] = ($statusCode === 0) ? BaseResponse::HTTP_FOUND : $statusCode;
        }

        /** @var Site $site */
        $site = Site::findOrFail($request->input('site_id'));

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $site, $params) {
            /** @var RedirectUrl $redirectUrl */
            $redirectUrl = $site->redirectUrls()->create($params);
            $created = $redirectUrl->fresh();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Update multiple redirect urls
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $rules = [
            'data' => 'required|array',
            'data.*.site_id' => 'sometimes|required|string|exists:sites,id',
            'data.*.source_url' => 'sometimes|required|string',
            'data.*.destination_url' => 'sometimes|required|string',
            'data.*.is_active' => 'sometimes|is_boolean',
            'data.*.status_code' => 'sometimes|nullable|numeric|between:301,399'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:redirect_urls,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedRedirectUrls = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var RedirectUrl $redirectUrl */
                $redirectUrl = RedirectUrl::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $redirectUrl);

                if (array_key_exists('site_id', $value)) {
                    /** @var Site $site */
                    $site = Site::findOrFail($value['site_id']);
                    $redirectUrl->site()->associate($site);
                }

                $value = collect($value)->except($this->idName)->toArray();

                $redirectUrl->update($value);

                array_push($ids, $redirectUrl->getKey());
            }
        });

        if ( ! empty($ids)) {
            /** @var RedirectUrl[]|\Illuminate\Support\Collection $updatedRedirectUrls */
            $updatedRedirectUrls = RedirectUrl::whereIn($this->idName, $ids)->get();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedRedirectUrls);
    }

    /**
     * Update a redirect url by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.site_id' => 'sometimes|required|string|exists:sites,id',
            'data.source_url' => 'sometimes|required|string',
            'data.destination_url' => 'sometimes|required|string',
            'data.is_active' => 'sometimes|is_boolean',
            'data.status_code' => 'sometimes|nullable|numeric|between:301,399'
        ]);

        /** @var RedirectUrl $redirectUrl */
        $redirectUrl = RedirectUrl::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $redirectUrl);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($redirectUrl, $data) {
            if (array_key_exists('site_id', $data)) {
                /** @var Site $site */
                $site = Site::findOrFail($data['site_id']);
                $redirectUrl->site()->associate($site);
            }

            $data = collect($data)->except($this->idName)->toArray();

            $redirectUrl->update($data);
        });

        /** @var RedirectUrl $updated */
        $updated = $redirectUrl->fresh();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Delete multiple redirect urls
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array',
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:redirect_urls,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var RedirectUrl $redirectUrl */
                $redirectUrl = RedirectUrl::findOrFail($value[$this->idName]);
                $this->authorizeForUser($this->auth->user(), 'delete', $redirectUrl);
                $redirectUrl->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a redirect url by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var RedirectUrl $redirectUrl */
        $redirectUrl = RedirectUrl::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $redirectUrl);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($redirectUrl) {
            $redirectUrl->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }
}
