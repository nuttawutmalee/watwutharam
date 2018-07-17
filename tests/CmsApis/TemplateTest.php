<?php

namespace Tests\CmsApis;

use App\Api\Models\Page;
use App\Api\Models\Site;
use App\Api\Models\Template;
use App\Api\Models\TemplateItem;
use Tests\CmsApiTestCase;

class TemplateTest extends CmsApiTestCase
{
    public function testGetTemplates()
    {
        factory(Template::class, 3)->create();
        $templates = Template::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/templates', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $templates
            ]);
    }

    public function testGetTemplateById()
    {
        $template = factory(Template::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/template/' . $template->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'id' => $template->id
                ]
            ]);
    }

    public function testStore()
    {
        $site = factory(Site::class)->create();
        $params = [
            'name' => 'MOCK-UP TEMPLATE',
            'variable_name' => self::randomVariableName(),
            'description' => self::$faker->sentence(),
            'site_id' => $site->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/template', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'site_id' => $params['site_id'],
                    'name' => 'MOCK-UP TEMPLATE'
                ]
            ]);

        $this->assertDatabaseHas('templates', [
            'site_id' => $params['site_id'],
            'name' => 'MOCK-UP TEMPLATE'
        ]);
    }

    public function testUpdate()
    {
        /** @var Template|Template[]|\Illuminate\Support\Collection $templates */
        $templates = factory(Template::class, 3)
            ->create()
            ->each(function ($item) {
                $item->name = 'UPDATED';
            });
        $data = $templates->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templates/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'name' => 'UPDATED'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('templates', [
            'name' => 'UPDATED'
        ]);
    }

    public function testUpdateChangeSiteId()
    {
        $site = factory(Site::class)->create();

        /** @var Template|Template[]|\Illuminate\Support\Collection $templates */
        $templates = factory(Template::class, 3)
            ->create()
            ->each(function ($item) use ($site) {
                $item->site_id = $site->id;
            });
        $data = $templates->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templates/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'site_id' => $site->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('templates', [
            'site_id' => $site->id
        ]);
    }

    public function testUpdateById()
    {
        $template = factory(Template::class)->create();
        $template->name = 'UPDATED';
        $data = $template->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/template/' . $template->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'UPDATED'
                ]
            ]);

        $this->assertDatabaseHas('templates', [
            'name' => 'UPDATED'
        ]);
    }

    public function testUpdateByIdChangeSiteId()
    {
        $site = factory(Site::class)->create();
        $template = factory(Template::class)->create();
        $template->site_id = $site->id;
        $data = $template->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/template/' . $template->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'site_id' => $site->id
                ]
            ]);

        $this->assertDatabaseHas('templates', [
            'site_id' => $site->id
        ]);
    }

    public function testDelete()
    {
        $templates = factory(Template::class, 3)->create();
        $data = $templates->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/templates', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('templates', [
            'id' => $templates->first()->id
        ]);
    }

    public function testDeleteById()
    {
        $template = factory(Template::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/template/' . $template->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('templates', [
            'id' => $template->id
        ]);
    }

    //Integrations
    public function testGetAllTemplateItemsByTemplateId()
    {
        $template = factory(Template::class)->create();
        $templateItems = factory(TemplateItem::class, 3)->create([
            'template_id' => $template->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/template/' . $template->id . '/templateItems', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $templateItems->toArray()
            ]);
    }

    public function testGetAllPagesByTemplateId()
    {
        $template = factory(Template::class)->create();
        $pages = factory(Page::class, 3)->create([
            'template_id' => $template->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/template/' . $template->id . '/pages', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'id' => $pages[0]->id
                    ]
                ]
            ]);
    }

    public function testTemplateItemCascadeDelete()
    {
        $template = factory(Template::class)->create();
        $templateItems = factory(TemplateItem::class, 3)->create([
            'template_id' => $template->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/template/' . $template->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('template_items', [
            'id' => $templateItems->first()->id,
            'template_id' => $template->id
        ]);
    }

    public function testPageCascadeDelete()
    {
        $template = factory(Template::class)->create();
        $pages = factory(Page::class, 3)->create([
            'template_id' => $template->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/template/' . $template->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('pages', [
            'id' => $pages->first()->id,
            'template_id' => $template->id
        ]);
    }

    public function testReorderTemplateItemsByTemplateId()
    {
        $template = factory(Template::class)->create();

        /** @var TemplateItem|TemplateItem[]|\Illuminate\Support\Collection $templateItems */
        $templateItems = factory(TemplateItem::class, 3)->create([
            'template_id' => $template->id
        ])->each(function ($item, $key) {
            /** @var TemplateItem $item */
            $item->name = 'USED TO BE NUMBER ' . ($key + 1);
            $item->save();
        });

        $templateItems->first()->display_order = 3;
        $templateItems->last()->display_order = 1;

        $data = $templateItems->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/template/' . $template->id . '/templateItems/reorder', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'USED TO BE NUMBER 1',
            'display_order' => 3
        ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'USED TO BE NUMBER 3',
            'display_order' => 1
        ]);
    }

    public function testReorderTemplateItemsByTemplateIdErrorWithTheSameOrder()
    {
        $template = factory(Template::class)->create();

        /** @var TemplateItem|TemplateItem[]|\Illuminate\Support\Collection $templateItems */
        $templateItems = factory(TemplateItem::class, 3)->create([
            'template_id' => $template->id
        ])->each(function ($item, $key) {
            /** @var TemplateItem $item */
            $item->name = 'USED TO BE NUMBER ' . ($key + 1);
            $item->save();
        });

        $templateItems->first()->display_order = 1;
        $templateItems->last()->display_order = 1;

        $data = $templateItems->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/template/' . $template->id . '/templateItems/reorder', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'USED TO BE NUMBER 1',
            'display_order' => 1
        ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'USED TO BE NUMBER 3',
            'display_order' => 3
        ]);
    }

    public function testReorderTemplateItemsByTemplateIdErrorWithMissingOrder()
    {
        $template = factory(Template::class)->create();

        /** @var TemplateItem|TemplateItem[]|\Illuminate\Support\Collection $templateItems */
        $templateItems = factory(TemplateItem::class, 3)->create([
            'template_id' => $template->id
        ])->each(function ($item, $key) {
            /** @var TemplateItem $item */
            $item->name = 'USED TO BE NUMBER ' . ($key + 1);
            $item->save();
        });

        $data = $templateItems->map(function ($item) {
            return collect($item)->except('display_order');
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/template/' . $template->id . '/templateItems/reorder', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'USED TO BE NUMBER 1',
            'display_order' => 1
        ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'USED TO BE NUMBER 3',
            'display_order' => 3
        ]);
    }
}
