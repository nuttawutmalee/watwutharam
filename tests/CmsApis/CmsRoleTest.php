<?php

namespace Tests\CmsApis;

use App\Api\Models\CmsRole;
use Tests\CmsApiTestCase;

class CmsRoleTest extends CmsApiTestCase
{
    public function testGetCmsRoles()
    {
        $roles = CmsRole::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/roles', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $roles
            ]);
    }
}

