<?php

namespace Tests\CmsApis;

use App\Api\Models\CategoryName;
use Tests\CmsApiTestCase;

class CategoryNameTest extends CmsApiTestCase
{
    public function testGetCategoryNames()
    {
        factory(CategoryName::class, 3)->create();
        $names = CategoryName::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/categoryNames', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $names
            ]);
    }
}

