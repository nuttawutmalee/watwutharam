<?php

namespace Tests\CmsApis;

use App\Api\Constants\RoleConstants;
use App\Api\Models\CmsRole;
use App\Api\Models\User;
use Tests\CmsApiTestCase;

class EditorialUserTest extends CmsApiTestCase
{
    /**
     * @var User
     */
    private $editor;

    /**
     * @var string
     */
    private $editorPassword = 'editorial';

    /**
     * @var array
     */
    private $editorAuthorizationHeader = [];

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();

        /** @var CmsRole $editorialRole */
        $editorialRole = CmsRole::where('name', RoleConstants::EDITORIAL)->first();
        $this->editor = factory(User::class)->create(['role_id' => $editorialRole->id, 'password' => $this->editorPassword]);
        $this->editorAuthorizationHeader = $this->getAuthorizationHeader($this->getToken($this->editor->email));
    }

    public function testGetUsers()
    {
        factory(User::class, 3)->create();
        $users = User::all()->toArray();

        $response = $this
            ->actingAs($this->editor)
            ->get(self::$apiPrefix . '/users', $this->editorAuthorizationHeader);

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
            ->actingAs($this->editor)
            ->get(self::$apiPrefix . '/user/' . $user->id, $this->editorAuthorizationHeader);

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
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/user/search-by-email', $data, $this->editorAuthorizationHeader);

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
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/user/search-by-email', $data, $this->editorAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);
    }

    public function testRegisterNewEditorialError()
    {
        /** @var CmsRole $editorialRole */
        $editorialRole = CmsRole::where('name', RoleConstants::EDITORIAL)->first();
        $params = [
            'name' => 'MOCK-UP USER',
            'email' => self::$faker->unique()->safeEmail,
            'password' => 'developers',
            'role_id' => $editorialRole->id
        ];
        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/user/register', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('users', [
            'email' => $params['email']
        ]);
    }

    public function testRegisterNewDeveloperError()
    {
        /** @var CmsRole $developerRole */
        $developerRole = CmsRole::where('name', RoleConstants::DEVELOPER)->first();
        $params = [
            'name' => 'MOCK-UP USER',
            'email' => self::$faker->unique()->safeEmail,
            'password' => 'developers',
            'role_id' => $developerRole->id
        ];
        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/user/register', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('users', [
            'email' => $params['email']
        ]);
    }

    public function testRegisterNewAdministratorError()
    {
        /** @var CmsRole $administratorRole */
        $administratorRole = CmsRole::where('name', RoleConstants::ADMINISTRATOR)->first();
        $params = [
            'name' => 'MOCK-UP USER',
            'email' => self::$faker->unique()->safeEmail,
            'password' => 'developers',
            'role_id' => $administratorRole->id
        ];
        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/user/register', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('users', [
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
        $params = [
            'email' => $this->editor->email,
            'password' => $this->editorPassword
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
                    'user_id' => $this->editor->id
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
        if ($token = $this->login($this->editor->email, $this->editorPassword)) {
            $header = self::getURLEncodedHeader(self::getAuthorizationHeader($token));

            $response = $this
                ->actingAs($this->editor)
                ->post(self::$apiPrefix . '/user/logout', [
                    'email' => $this->editor->email
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
        if ($token = $this->login($this->editor->email, $this->editorPassword)) {
            $header = self::getURLEncodedHeader(self::getAuthorizationHeader($token));

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

        if ($token = $this->login($this->editor->email, $this->editorPassword)) {
            $header = self::getURLEncodedHeader(self::getAuthorizationHeader($token));

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

    public function testUpdateArrayOfEditorialsError()
    {
        /** @var CmsRole $editorialRole */
        $editorialRole = CmsRole::where('name', RoleConstants::EDITORIAL)->first();
        $editors = factory(User::class, 3)->create(['role_id' => $editorialRole->id]);
        $data = $editors->each(function ($item) {
            $item->name = 'updated name';
            $item->is_active = false;
        })->toArray();

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/users/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $editors->first()->id,
            'name' => 'updated name',
            'is_active' => false
        ]);
    }

    public function testUpdateArrayOfItself()
    {
        $updatedEditor = $this->editor;
        $updatedEditor->name = 'updated name';
        $data[] = $updatedEditor->toArray();

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/users/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $updatedEditor->id,
            'name' => 'updated name'
        ]);
    }

    public function testUpdateArrayOfEditorialDeveloperAndAdministratorError()
    {
        $data = [];

        /** @var CmsRole $editorialRole */
        $editorialRole = CmsRole::where('name', RoleConstants::EDITORIAL)->first();
        $editor = factory(User::class)->create(['role_id' => $editorialRole->id]);
        $editorNameBeforeChanges = $editor->name;
        $editor->name = 'updated name';

        $data[] = $editor->toArray();

        /** @var CmsRole $developerRole */
        $developerRole = CmsRole::where('name', RoleConstants::DEVELOPER)->first();
        $developer = factory(User::class)->create(['role_id' => $developerRole->id]);
        $developerNameBeforeChanges = $developer->name;
        $developer->name = 'updated name';

        $data[] = $developer->toArray();

        /** @var CmsRole $administratorRole */
        $administratorRole = CmsRole::where('name', RoleConstants::ADMINISTRATOR)->first();
        $administrator = factory(User::class)->create(['role_id' => $administratorRole->id]);
        $administratorNameBeforeChanges = $administrator->name;
        $administrator->name = 'updated name';

        $data[] = $administrator->toArray();

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/users/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $developer->id,
            'name' => $developerNameBeforeChanges
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
            'name' => $administratorNameBeforeChanges
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $editor->id,
            'name' => $editorNameBeforeChanges
        ]);
    }

    public function testUpdateEditorialByIdError()
    {
        /** @var CmsRole $editorialRole */
        $editorialRole = CmsRole::where('name', RoleConstants::EDITORIAL)->first();
        $editor = factory(User::class)->create(['role_id' => $editorialRole->id]);
        $editor->name = 'updated name';
        $editor->is_active = false;
        $data = $editor->toArray();

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/user/' . $editor->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $editor->id,
            'name' => 'updated name',
            'is_active' => false
        ]);
    }

    public function testUpdateItselfByIdError()
    {
        $updatedEditor = $this->editor;
        $updatedEditor->name = 'updated name';
        $data = $updatedEditor->toArray();

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/user/' . $updatedEditor->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $updatedEditor->id,
            'name' => 'updated name'
        ]);
    }

    public function testUpdateDeveloperByIdError()
    {
        /** @var CmsRole $developerRole */
        $developerRole = CmsRole::where('name', RoleConstants::DEVELOPER)->first();
        $developer = factory(User::class)->create(['role_id' => $developerRole->id]);
        $developerNameBeforeChanges = $developer->name;
        $developer->name = 'updated name';

        $data = $developer->toArray();

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/user/' . $developer->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $developer->id,
            'name' => $developerNameBeforeChanges
        ]);
    }

    public function testUpdateAdministratorByIdError()
    {
        /** @var CmsRole $administratorRole */
        $administratorRole = CmsRole::where('name', RoleConstants::ADMINISTRATOR)->first();
        $admin = factory(User::class)->create(['role_id' => $administratorRole->id]);
        $adminNameBeforeChanges = $admin->name;
        $admin->name = 'updated name';

        $data = $admin->toArray();

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->post(self::$apiPrefix . '/user/' . $admin->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => $adminNameBeforeChanges
        ]);
    }

    public function testDeleteArrayOfEditorialsError()
    {
        /** @var CmsRole $editorialRole */
        $editorialRole = CmsRole::where('name', RoleConstants::EDITORIAL)->first();
        $editors = factory(User::class, 3)->create(['role_id' => $editorialRole->id]);
        $data = $editors->toArray();

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->delete(self::$apiPrefix . '/users', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $editors->first()->id
        ]);
    }

    public function testDeleteArrayOfItselfError()
    {
        $updatedEditor = $this->editor;
        $data[] = $updatedEditor->toArray();

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->delete(self::$apiPrefix . '/users', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $updatedEditor->id
        ]);
    }

    public function testDeleteArrayOfEditorialDeveloperAndAdministratorError()
    {
        $data = [];

        /** @var CmsRole $editorialRole */
        $editorialRole = CmsRole::where('name', RoleConstants::EDITORIAL)->first();
        $editor = factory(User::class)->create(['role_id' => $editorialRole->id]);

        $data[] = $editor->toArray();

        /** @var CmsRole $developerRole */
        $developerRole = CmsRole::where('name', RoleConstants::DEVELOPER)->first();
        $developer = factory(User::class)->create(['role_id' => $developerRole->id]);

        $data[] = $developer->toArray();

        /** @var CmsRole $administratorRole */
        $administratorRole = CmsRole::where('name', RoleConstants::ADMINISTRATOR)->first();
        $administrator = factory(User::class)->create(['role_id' => $administratorRole->id]);

        $data[] = $administrator->toArray();

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->delete(self::$apiPrefix . '/users', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $developer->id
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $administrator->id
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $editor->id
        ]);
    }

    public function testDeleteEditorialByIdError()
    {
        /** @var CmsRole $editorialRole */
        $editorialRole = CmsRole::where('name', RoleConstants::EDITORIAL)->first();
        $editor = factory(User::class)->create(['role_id' => $editorialRole->id]);

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->delete(self::$apiPrefix . '/user/' . $editor->id, [], $header);

        $response
            ->assertJson([
                'result' => false,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $editor->id
        ]);
    }

    public function testDeleteItselfById()
    {
        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->delete(self::$apiPrefix . '/user/' . $this->editor->id, [], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $this->editor->id
        ]);
    }

    public function testDeleteDeveloperByIdError()
    {
        /** @var CmsRole $developerRole */
        $developerRole = CmsRole::where('name', RoleConstants::DEVELOPER)->first();
        $developer = factory(User::class)->create(['role_id' => $developerRole->id]);

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->delete(self::$apiPrefix . '/user/' . $developer->id, [], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $developer->id
        ]);
    }

    public function testDeleteAdministratorByIdError()
    {
        /** @var CmsRole $administratorRole */
        $administratorRole = CmsRole::where('name', RoleConstants::ADMINISTRATOR)->first();
        $admin = factory(User::class)->create(['role_id' => $administratorRole->id]);

        $header = self::getURLEncodedHeader($this->editorAuthorizationHeader);

        $response = $this
            ->actingAs($this->editor)
            ->delete(self::$apiPrefix . '/user/' . $admin->id, [], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $admin->id
        ]);
    }
}