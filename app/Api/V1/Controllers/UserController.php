<?php

namespace App\Api\V1\Controllers;

use App\Api\Constants\ErrorMessageConstants;
use App\Api\Models\CmsRole;
use App\Api\Models\User;
use App\Api\Models\Site;
use App\Api\Models\CmsLog;
use App\Api\Constants\LogConstants;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class UserController extends BaseController
{
    /**
     * UserController constructor.
     */
    function __construct()
    {
        $this->idName = (new User)->getKeyName();
    }

    /**
     * Return all users
     *
     * @param Request $request
     * @return mixed
     */
    public function getUsers(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(User::all());
    }

    /**
     * Return a user by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function getUserById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var User $user */
        $user = User::findOrFail($id);
        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($user);
    }

    /**
     * Return a user by its email
     *
     * @param Request $request
     * @return mixed
     */
    public function getUserByEmail(/** @noinspection PhpUnusedParameterInspection */ Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'email' => 'required|email'
        ]);

        /** @var User $user */
        if ($user = User::where('email', $request->input('email'))->first()) {
            /** @noinspection PhpUndefinedMethodInspection */
            return response()->apiJson($user);
        } else {
            /** @noinspection PhpUndefinedMethodInspection */
            return response()->apiJson(null);
        }
    }

    /**
     * Log in a user
     *
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'email' => 'required|email|exists:users',
            'password' => 'required|string'
        ]);

        $credentials = $request->only(['email', 'password']);

        /** @noinspection PhpUndefinedMethodInspection */
        if ( ! $token = JWTAuth::attempt($credentials)) {
            /** @noinspection PhpUndefinedMethodInspection */
            return response()->apiJson(null, ErrorMessageConstants::INVALID_LOGIN, BaseResponse::HTTP_UNAUTHORIZED);
        }

        // Get translated text max length
        /** @noinspection PhpUndefinedMethodInspection */
        $databaseName = DB::getDatabaseName();
        $translatedTextLimit = null;
        $translatedTextType = null;

        /** @noinspection PhpUndefinedMethodInspection */
        try {
            $mysqlStatus = DB::select('DESCRIBE ' . $databaseName . '.site_translations translated_text');
            if (count($mysqlStatus) > 0) {
                if ($translatedTextStatus = $mysqlStatus[0]) {
                    $translatedTextType = isset($translatedTextStatus->Type) ? $translatedTextStatus->Type : null;
                }
            }
        } catch (\Exception $exception) {
            try {
                $sqliteStatus = DB::select('PRAGMA table_info([site_translations]);');
                if (count($sqliteStatus) > 0) {
                    if ($translatedTextStatus = collect($sqliteStatus)->where('name', 'translated_text')->first()) {
                        $translatedTextType = isset($translatedTextStatus->type) ? $translatedTextStatus->type : null;
                    }
                }
            } catch (\Exception $exception) {}
        }

        switch ($translatedTextType) {
            case 'text':
                $translatedTextLimit = 65535;
                break;
            case 'mediumtext':
                $translatedTextLimit = 16777215;
                break;
            case 'longtext':
                $translatedTextLimit = 4294967295;
                break;
            default:
                break;
        }

        /** @var User $user */
        /** @noinspection PhpUndefinedMethodInspection */
        $user = User::where('email', $request->input('email'))
            ->isActive()
            ->firstOrFail();

        /** @var CmsRole $role */
        $role = $user->role;

        CmsLog::log($user, LogConstants::USER_LOGIN);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson([
            "token" => $token,
            "form_token" => config('cms.' . get_cms_application() . '.form_token'),
            "translated_text_limit" => $translatedTextLimit,
            "user_id" => $user->{$this->idName},
            "email" => $user->email,
            "fullname" => $user->name,
            "role" => $role
        ]);
    }

    /**
     * Store a new user
     *
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
            'role_id' => 'required|string|exists:cms_roles,id',
            'is_active' => 'sometimes|is_boolean'
        ]);

        /** @var CmsRole $role */
        $role = CmsRole::findOrFail($request->input('role_id'));

        $this->authorizeForUser($this->auth->user(), 'create', [User::class, $role]);

        $params = $request->only(['name', 'email', 'password', 'role_id']);

        if ($request->exists('is_active')) {
            $params['is_active'] = $request->input('is_active');
        }

        $created = null;

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use (&$created, $params) {
            /** @var User $user */
            $user = User::create($params);
            $created = $user->fresh();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($created);
    }

    /**
     * Log out a user
     *
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'email' => 'required|email|exists:users'
        ]);

        /** @noinspection PhpUndefinedMethodInspection */
        User::where('email', $request->input('email'))
            ->isActive()
            ->firstOrFail();

        /** @noinspection PhpUndefinedMethodInspection */
        $token = JWTAuth::getToken();
        /** @noinspection PhpUndefinedMethodInspection */
        JWTAuth::invalidate($token, true);

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Return auth data if the user is authorized
     *
     * @param Request $request
     * @return mixed
     */
    public function checkAuth(Request $request)
    {
        $this->guardAgainstInvalidateRequest($request->all(), [
            'email' => 'required|email|exists:users'
        ]);

        /** @var User $user */
        $user = User::where('email', $request->input('email'))->firstOrFail();

        /** @var CmsRole $role */
        $role = $user->role;

        /** @var CmsRole[]|\Illuminate\Support\Collection $all_roles */
        $all_roles = CmsRole::all();

        /** @var Site[]|\Illuminate\Support\Collection $all_sites */
        $all_sites = Site::all();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson([
            "user_id" => $user->{$this->idName},
            "email" => $user->email,
            "fullname" => $user->name,
            "role" => $role,
            "all_roles" => $all_roles,
            "all_sites" => $all_sites
        ]);
    }

    /**
     * Update multiple users
     *
     * @param Request $request
     * @return mixed
     */
    public function update(Request $request)
    {
        $rules = [
            'data' => 'required|array',
            'data.*.name' => 'sometimes|required|string',
            'data.*.password' => 'sometimes|required|string',
            'data.*.role_id' => 'sometimes|required|string|exists:cms_roles,id',
            'data.*.is_active' => 'sometimes|is_boolean'
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:users,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        $updatedUsers = [];
        $ids = [];

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data, &$ids) {
            foreach ($data as $key => $value) {
                /** @var User $user */
                $user = User::findOrFail($value[$this->idName]);

                $this->authorizeForUser($this->auth->user(), 'update', $user);

                $this->guardAgainstInvalidateRequest($value, [
                    'email' => [
                        'sometimes',
                        'required',
                        'email',
                        Rule::unique('users', 'email')->ignore($user->{$this->idName}, $this->idName)
                    ]
                ]);

                if (array_key_exists('role_id', $value)) {
                    /** @var CmsRole $role */
                    $role = CmsRole::findOrFail($value['role_id']);
                    $role->users()->save($user);
                }

                $value = collect($value)->except($this->idName)->toArray();

                $user->update($value);

                array_push($ids, $user->getKey());
            }
        });

        if ( ! empty($ids)) {
            /** @var User[]|\Illuminate\Support\Collection $updatedUsers */
            $updatedUsers = User::whereIn($this->idName, $ids)->get();
        }

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updatedUsers);
    }

    /**
     * Update a user by id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function updateById(Request $request, $id)
    {
        /** @var User $user */
        $user = User::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'update', $user);

        $this->guardAgainstInvalidateRequest($request->all(), [
            'data' => 'required|array',
            'data.name' => 'sometimes|required|string',
            'data.email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->{$this->idName}, $this->idName)
            ],
            'data.password' => 'sometimes|required|string',
            'data.role_id' => 'sometimes|required|string|exists:cms_roles,id',
            'data.is_active' => 'sometimes|is_boolean'
        ]);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($user, $data) {
            if (array_key_exists('role_id', $data)) {
                /** @var CmsRole $role */
                $role = CmsRole::findOrFail($data['role_id']);
                $role->users()->save($user);
            }

            $data = collect($data)->except($this->idName)->toArray();

            $user->update($data);
        });

        /** @var User $updated */
        $updated = $user->fresh();

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson($updated);
    }

    /**
     * Delete multiple users
     *
     * @param Request $request
     * @return mixed
     */
    public function delete(Request $request)
    {
        $rules = [
            'data' => 'required|array',
        ];
        $rules['data.*.' . $this->idName] = 'required|string|distinct|exists:users,' . $this->idName;
        $this->guardAgainstInvalidateRequest($request->all(), $rules);

        $data = $request->input('data');

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($data) {
            foreach ($data as $key => $value) {
                /** @var User $user */
                $user = User::findOrFail($value[$this->idName]);
                $this->authorizeForUser($this->auth->user(), 'delete', $user);
                $user->delete();
            }
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }

    /**
     * Delete a user by its id
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function deleteById(/** @noinspection PhpUnusedParameterInspection */ Request $request, $id)
    {
        /** @var User $user */
        $user = User::findOrFail($id);

        $this->authorizeForUser($this->auth->user(), 'delete', $user);

        /** @noinspection PhpUndefinedMethodInspection */
        DB::transaction(function () use ($user) {
            $user->delete();
        });

        /** @noinspection PhpUndefinedMethodInspection */
        return response()->apiJson(null);
    }
}
