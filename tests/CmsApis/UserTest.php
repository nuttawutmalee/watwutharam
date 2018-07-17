<?php

namespace Tests\CmsApis;

use App\Api\Models\CmsRole;
use App\Api\Models\User;
use Tests\CmsApiTestCase;

class UserTest extends CmsApiTestCase
{
    public function testGetUsers()
    {
        factory(User::class, 3)->create();
        $users = User::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/users', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $users
            ]);
    }

    public function testGetUserById()
    {
        $user = $this->mockUser();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/user/' . $user->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'id' => $user->id
                ]
            ]);
    }

    public function testGetUserByEmail()
    {
        $user = $this->mockUser();

        $data = collect($user)->only('email')->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/user/search-by-email', $data, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'id' => $user->id
                ]
            ]);
    }

    public function testGetUnknownUserByEmail()
    {
        $data = ['email' => self::$faker->unique()->safeEmail];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/user/search-by-email', $data, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);
    }

    public function testRegister()
    {
        $role = CmsRole::all()->random();
        $params = [
            'name' => 'MOCK-UP USER',
            'email' => self::$faker->unique()->safeEmail,
            'password' => 'developers',
            'role_id' => $role->id
        ];
        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/user/register', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'email' => $params['email']
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $params['email']
        ]);
    }

    public function testRegisterErrorWithoutRole()
    {
        $params = [
            'name' => 'MOCK UP USER',
            'email' => self::$faker->unique()->safeEmail,
            'password' => 'developers'
        ];
        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/user/register', $params, $header);

        $response
            ->assertStatus(500)
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('users', [
            'email' => $params['email']
        ]);
    }

    public function testLogin()
    {
        $user = $this->mockUser();
        $params = [
            'email' => $user->email,
            'password' => 'developers'
        ];
        $header = self::getURLEncodedHeader();

        $response = $this->post(self::$apiPrefix . '/user/login', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    'token', 'user_id'
                ]
            ])
            ->assertJson([
                'result' => true,
                'data' => [
                    'user_id' => $user->id
                ]
            ]);
    }

    public function testInvalidLogin()
    {
        $params = [
            'email' => 'wrongemail@email.com',
            'password' => 'wrongpassword'
        ];
        $header = self::getURLEncodedHeader();

        $response = $this->post(self::$apiPrefix . '/user/login', $params, $header);

        $response
            ->assertJsonStructure([
                'message'
            ])
            ->assertJson([
                'result' => false
            ]);
    }

    public function testLogout()
    {
        if ($token = $this->login()) {
            $header = $this->getAuthorizationHeader($token);
            $header = self::getURLEncodedHeader($header);

            $response = $this
                ->actingAs(self::$developer)
                ->post(self::$apiPrefix . '/user/logout', [
                    'email' => self::$developer->email
                ], $header);

            $response
                ->assertSuccessful()
                ->assertJson([
                    'result' => true,
                    'data' => null
                ]);
        } else {
            $this->incomplete('TOKEN INVALID');
        }
    }

    public function testInvalidLogout()
    {
        if ($token = $this->login()) {
            $header = $this->getAuthorizationHeader($token);
            $header = self::getURLEncodedHeader($header);

            $response = $this
                ->actingAs(self::$developer)
                ->post(self::$apiPrefix . '/user/logout', [
                    'email' => 'wrongemail@email.com'
                ], $header);

            $response
                ->assertJsonStructure([
                    'result', 'message'
                ])
                ->assertJson([
                    'result' => false
                ]);
        } else {
            $this->incomplete('TOKEN INVALID');
        }
    }

    public function testIsLoggedIn()
    {
        $user = $this->mockUser();

        if ($token = $this->login($user->email, 'developers')) {
            $header = $this->getAuthorizationHeader($token);
            $header = self::getURLEncodedHeader($header);

            $response = $this
                ->actingAs($user)
                ->post(self::$apiPrefix . '/user/is-loggedin', [
                    'email' => $user->email
                ], $header);

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'data' => [
                        'user_id',
                        'role' => [
                            'is_developer',
                            'allow_structure',
                            'allow_content',
                            'allow_user'
                        ]
                    ]
                ])
                ->assertJson([
                    'result' => true,
                    'data' => [
                        'user_id' => $user->id
                    ]
                ]);
        } else {
            $this->incomplete('TOKEN INVALID');
        }
    }

    public function testUpdate()
    {
        $users = factory(User::class, 3)->create();
        $data = $users->each(function ($item) {
            $item->name = 'updated name';
            $item->is_active = false;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/users/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'name' => 'updated name',
                        'is_active' => false
                    ]
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $users->first()->id,
            'name' => 'updated name',
            'is_active' => false
        ]);
    }

    public function testDelete()
    {
        $users = factory(User::class, 3)->create();
        $data = $users->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/users', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $users->first()->id
        ]);
    }

    public function testUpdateById()
    {
        $user = factory(User::class)->create();
        $user->name = 'updated name';
        $user->is_active = false;
        $data = $user->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/user/' . $user->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'updated name',
                    'is_active' => false
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'updated name',
            'is_active' => false
        ]);
    }

    public function testDeleteById()
    {
        $user = factory(User::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/user/' . $user->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }
}