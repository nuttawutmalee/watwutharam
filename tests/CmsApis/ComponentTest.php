<?php

namespace Tests\CmsApis;

use App\Api\Constants\OptionValueConstants;
use App\Api\Models\Component;
use App\Api\Models\ComponentOption;
use App\Api\Models\GlobalItem;
use App\Api\Models\PageItem;
use App\Api\Models\TemplateItem;
use Tests\CmsApiTestCase;

class ComponentTest extends CmsApiTestCase
{
    public function testGetComponents()
    {
        factory(Component::class, 3)->create();
        $components = Component::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/components', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $components
            ]);
    }

    public function testGetComponentById()
    {
        $component = factory(Component::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/component/' . $component->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'id' => $component->id
                ]
            ]);
    }

    public function testStore()
    {
        $params = [
            'name' => 'MOCK-UP COMPONENT',
            'variable_name' => self::randomVariableName()
        ];
        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/component', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => $params['name'],
                    'variable_name' => $params['variable_name']
                ]
            ]);

        $this->assertDatabaseHas('components', [
            'name' => $params['name'],
            'variable_name' => $params['variable_name']
        ]);
    }

    public function testUpdate()
    {
        $components = factory(Component::class, 3)->create();
        $data = $components->each(function ($item) {
            $item->name = 'UPDATED MOCK-UP COMPONENT';
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/components/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'name' => 'UPDATED MOCK-UP COMPONENT'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('components', [
            'id' => $components->first()->id,
            'name' => 'UPDATED MOCK-UP COMPONENT'
        ]);
    }

    public function testDelete()
    {
        $components = factory(Component::class, 3)->create();
        $data = $components->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/components', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('components', [
            'id' => $components->first()->id
        ]);
    }

    public function testUpdateById()
    {
        $component = factory(Component::class)->create();
        $component->name = 'UPDATED MOCK-UP COMPONENT';
        $data = $component->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/component/' . $component->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'UPDATED MOCK-UP COMPONENT'
                ]
            ]);

        $this->assertDatabaseHas('components', [
            'id' => $component->id,
            'name' => 'UPDATED MOCK-UP COMPONENT'
        ]);
    }

    public function testDeleteById()
    {
        $component = factory(Component::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/component/' . $component->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('components', [
            'id' => $component->id
        ]);
    }

    //Integrations
    public function testComponentOptionCascadeDelete()
    {
        $component = factory(Component::class)->create();
        $componentOptions = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/component/' . $component->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('components', [
            'id' => $component->id
        ]);

        $this->assertDatabaseMissing('component_options', [
            'id' => $componentOptions->first()->id
        ]);
    }

    public function testGlobalItemSetNullDelete()
    {
        $component = factory(Component::class)->create();
        $globalItems = factory(GlobalItem::class, 3)->create([
            'component_id' => $component->id
        ]);

        $this->assertDatabaseHas('global_items', [
            'id' => $globalItems->first()->id,
            'component_id' => $component->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/component/' . $component->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('components', [
            'id' => $component->id
        ]);

        $this->assertDatabaseHas('global_items', [
            'id' => $globalItems->first()->id,
            'component_id' => null
        ]);
    }

    public function testTemplateItemSetNullDelete()
    {
        $component = factory(Component::class)->create();
        $templateItems = factory(TemplateItem::class, 3)->create([
            'component_id' => $component->id
        ]);

        $this->assertDatabaseHas('template_items', [
            'id' => $templateItems->first()->id,
            'component_id' => $component->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/component/' . $component->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('components', [
            'id' => $component->id
        ]);

        $this->assertDatabaseHas('template_items', [
            'id' => $templateItems->first()->id,
            'component_id' => null
        ]);
    }

    public function testPageItemSetNullDelete()
    {
        $component = factory(Component::class)->create();
        $pageItems = factory(PageItem::class, 3)->create([
            'component_id' => $component->id
        ]);

        $this->assertDatabaseHas('page_items', [
            'id' => $pageItems->first()->id,
            'component_id' => $component->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/component/' . $component->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('components', [
            'id' => $component->id
        ]);

        $this->assertDatabaseHas('page_items', [
            'id' => $pageItems->first()->id,
            'component_id' => null
        ]);
    }

    public function testGetComponentOptionsByComponentId()
    {
        $component = factory(Component::class)->create();

        /** @var ComponentOption|ComponentOption[] $componentOptions */
        $componentOptions = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->string()->create([
                'option_value' => 'TEST'
            ]);
        });

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/component/' . $component->id . '/componentOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'id' => $componentOptions->first()->id,
                        'option_type' => OptionValueConstants::STRING,
                        'option_value' => 'TEST'
                    ]
                ]
            ]);
    }
}
