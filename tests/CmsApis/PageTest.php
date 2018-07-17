<?php

namespace Tests\CmsApis;

use App\Api\Models\Page;
use App\Api\Models\PageItem;
use App\Api\Models\Site;
use App\Api\Models\Template;
use Tests\CmsApiTestCase;

class PageTest extends CmsApiTestCase
{
    public function testGetAllPages()
    {
        factory(Page::class, 3)->create();
        $pages = Page::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/pages', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $pages
            ]);
    }

    public function testGetPageById()
    {
        $page = factory(Page::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/page/' . $page->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'id' => $page->id
                ]
            ]);
    }

    public function testStoreWithoutTemplateError()
    {
        $params = [
            'name' => 'MOCK-UP PAGE',
            'variable_name' => self::randomVariableName(),
            'friendly_url' => self::$faker->slug,
            'description' => self::$faker->sentence()
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('pages', [
            'name' => $params['name'],
            'friendly_url' => $params['friendly_url']
        ]);
    }

    public function testStoreWithTemplate()
    {
        $template = factory(Template::class)->create();
        $params = [
            'name' => 'MOCK-UP PAGE',
            'variable_name' => self::randomVariableName(),
            'friendly_url' => self::$faker->slug,
            'description' => self::$faker->sentence(),
            'template_id' => $template->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => $params['name'],
                    'friendly_url' => $params['friendly_url'],
                    'template_id' => $template->id
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => $params['name'],
            'friendly_url' => $params['friendly_url'],
            'template_id' => $template->id
        ]);
    }

    public function testUpdate()
    {
        /** @var Page[]|\Illuminate\Support\Collection $pages */
        $pages = factory(Page::class, 3)
            ->create()
            ->each(function ($item) {
                $item->name = 'UPDATED';
            });
        $data = $pages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

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

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);
    }

    public function testUpdateChangeTemplateId()
    {
        $template = factory(Template::class)->create();

        /** @var Page[]|\Illuminate\Support\Collection $pages */
        $pages = factory(Page::class, 3)
            ->create()
            ->each(function ($item) use ($template) {
                $item->template_id = $template->id;
            });
        $data = $pages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'template_id' => $template->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'template_id' => $template->id
        ]);
    }

    public function testUpdateById()
    {
        $page = factory(Page::class)->create();
        $page->name = 'UPDATED';
        $data = $page->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'UPDATED'
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);
    }

    public function testUpdateByIdChangeTemplateId()
    {
        $template = factory(Template::class)->create();
        $page = factory(Page::class)->create();
        $page->template_id = $template->id;
        $data = $page->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'template_id' => $template->id
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'template_id' => $template->id
        ]);
    }

    public function testDelete()
    {
        $pages = factory(Page::class, 3)->create();
        $data = $pages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/pages', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('pages', [
            'id' => $pages->first()->id
        ]);
    }

    public function testDeleteById()
    {
        $page = factory(Page::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/page/' . $page->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('pages', [
            'id' => $page->id
        ]);
    }

    //Integrations
    public function testGetAllPageItemsByPageId()
    {
        $page = factory(Page::class)->create();
        factory(PageItem::class, 3)->create([
            'page_id' => $page->id
        ]);
        
        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/page/' . $page->id . '/pageItems', self::$developerAuthorizationHeader);
        
        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ]);
    }

    public function testPageItemCascadeDelete()
    {
        $page = factory(Page::class)->create();
        $pageItems = factory(PageItem::class, 3)->create([
            'page_id' => $page->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/page/' . $page->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);
        
        $this->assertDatabaseMissing('page_items', [
            'id' => $pageItems->first()->id
        ]);
    }

    public function testReorderPageItemsByPageId()
    {
        $page = factory(Page::class)->create();

        /** @var PageItem[]|PageItem|\Illuminate\Support\Collection $pageItems */
        $pageItems = factory(PageItem::class, 3)->create([
            'page_id' => $page->id
        ])->each(function ($item, $key) {
            /** @var PageItem $item */
            $item->name = 'USED TO BE NUMBER ' . ($key + 1);
            $item->save();
        });

        $pageItems->first()->display_order = 3;
        $pageItems->last()->display_order = 1;

        $data = $pageItems->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/pageItems/reorder', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'USED TO BE NUMBER 1',
            'display_order' => 3
        ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'USED TO BE NUMBER 3',
            'display_order' => 1
        ]);
    }

    public function testReorderPageItemsByPageIdErrorWithTheSameOrder()
    {
        $page = factory(Page::class)->create();

        /** @var PageItem[]|PageItem|\Illuminate\Support\Collection $pageItems */
        $pageItems = factory(PageItem::class, 3)->create([
            'page_id' => $page->id
        ])->each(function ($item, $key) {
            /** @var PageItem $item */
            $item->name = 'USED TO BE NUMBER ' . ($key + 1);
            $item->save();
        });

        $pageItems->first()->display_order = 1;
        $pageItems->last()->display_order = 1;

        $data = $pageItems->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/pageItems/reorder', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'USED TO BE NUMBER 1',
            'display_order' => 1
        ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'USED TO BE NUMBER 3',
            'display_order' => 3
        ]);
    }

    public function testReorderPageItemsByPageIdErrorWithMissingOrder()
    {
        $page = factory(Page::class)->create();

        /** @var PageItem[]|PageItem|\Illuminate\Support\Collection $pageItems */
        $pageItems = factory(PageItem::class, 3)->create([
            'page_id' => $page->id
        ])->each(function ($item, $key) {
            /** @var PageItem $item */
            $item->name = 'USED TO BE NUMBER ' . ($key + 1);
            $item->save();
        });

        $data = $pageItems->map(function ($item) {
            return collect($item)->except('display_order');
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/pageItems/reorder', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'USED TO BE NUMBER 1',
            'display_order' => 1
        ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'USED TO BE NUMBER 3',
            'display_order' => 3
        ]);
    }

    //Parent
    //Create with non-site parent page
    public function testCreatePageWithNonSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);

        $differentSite = factory(Site::class)->create();
        $differentTemplate = factory(Template::class)->create(['site_id' => $differentSite->id]);
        $differentParentPage = factory(Page::class)->create(['template_id' => $differentTemplate->id]);

        $params = [
            'name' => 'MOCK-UP PAGE',
            'variable_name' => self::randomVariableName(),
            'friendly_url' => self::$faker->slug,
            'description' => self::$faker->sentence(),
            'template_id' => $mainTemplate->id,
            'parent_ids' => [$differentParentPage->id]
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('pages', [
            'name' => $params['name'],
            'friendly_url' => $params['friendly_url'],
            'template_id' => $mainTemplate->id
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'parent_id' => $differentParentPage->id
        ]);
    }

    //Create with non-site parent page array
    public function testCreatePageWithNonSiteParentPageArray()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);

        $differentSite = factory(Site::class)->create();
        $differentTemplate = factory(Template::class)->create(['site_id' => $differentSite->id]);
        $differentParentPages = factory(Page::class, 3)->create(['template_id' => $differentTemplate->id]);

        $params = [
            'name' => 'MOCK-UP PAGE',
            'variable_name' => self::randomVariableName(),
            'friendly_url' => self::$faker->slug,
            'description' => self::$faker->sentence(),
            'template_id' => $mainTemplate->id,
            'parent_ids' => $differentParentPages->pluck('id')->toArray()
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('pages', [
            'name' => $params['name'],
            'friendly_url' => $params['friendly_url'],
            'template_id' => $mainTemplate->id
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'parent_id' => $differentParentPages->first()->id
        ]);
    }

    //Create with site parent page
    public function testCreatePageWithSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        $params = [
            'name' => 'MOCK-UP PAGE',
            'variable_name' => self::randomVariableName(),
            'friendly_url' => self::$faker->slug,
            'description' => self::$faker->sentence(),
            'template_id' => $mainTemplate->id,
            'parent_ids' => [$mainParentPage->id]
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page', $params, $header);

        /** @var \Dingo\Api\Http\Response|\Illuminate\Foundation\Testing\TestResponse $response */
        $content = json_decode($response->getContent());
        $data = $content->data;

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'parents' => [
                        [
                            'id' => $mainParentPage->id
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => $params['name'],
            'friendly_url' => $params['friendly_url'],
            'template_id' => $mainTemplate->id
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $data->id,
            'parent_id' => $mainParentPage->id
        ]);
    }

    public function testCreatePageWithSiteParentPageArray()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPages = factory(Page::class, 3)->create(['template_id' => $mainTemplate->id]);

        $params = [
            'name' => 'MOCK-UP PAGE',
            'variable_name' => self::randomVariableName(),
            'friendly_url' => self::$faker->slug,
            'description' => self::$faker->sentence(),
            'template_id' => $mainTemplate->id,
            'parent_ids' => $mainParentPages->pluck('id')->toArray()
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page', $params, $header);

        /** @var \Dingo\Api\Http\Response|\Illuminate\Foundation\Testing\TestResponse $response */
        $content = json_decode($response->getContent());
        $data = $content->data;

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'id' => $mainParentPages->first()->id
            ])
            ->assertJsonFragment([
                'id' => $mainParentPages->last()->id
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => $params['name'],
            'friendly_url' => $params['friendly_url'],
            'template_id' => $mainTemplate->id
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $data->id,
            'parent_id' => $mainParentPages->first()->id
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $data->id,
            'parent_id' => $mainParentPages->last()->id
        ]);
    }

    //Update no -> non site parent page
    public function testUpdatePagesWithNoParentPageToNonSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);

        $differentSite = factory(Site::class)->create();
        $differentTemplate = factory(Template::class)->create(['site_id' => $differentSite->id]);
        $differentParentPage = factory(Page::class)->create(['template_id' => $differentTemplate->id]);

        /** @var Page|Page[]|\Illuminate\Support\Collection $pages */
        $pages = factory(Page::class, 3)
            ->create(['template_id' => $mainTemplate->id])
            ->each(function ($item) use ($differentParentPage) {
                $item['parent_ids'] = [$differentParentPage->id];
                $item->name = 'UPDATED';
            });
        $data = $pages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $pages->first()->id,
            'parent_id' => $differentParentPage->id
        ]);
    }

    public function testUpdatePageByIdWithNoParentPageToNonSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);

        $differentSite = factory(Site::class)->create();
        $differentTemplate = factory(Template::class)->create(['site_id' => $differentSite->id]);
        $differentParentPage = factory(Page::class)->create(['template_id' => $differentTemplate->id]);

        $page = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
        $page['parent_ids'] = [$differentParentPage->id];
        $page->name = 'UPDATED';
        $data = $page->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $page->id,
            'parent_id' => $differentParentPage->id
        ]);
    }

    //Update no -> site parent page
    public function testUpdatePagesWithNoParentPageToSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        /** @var Page|Page[]|\Illuminate\Support\Collection $pages */
        $pages = factory(Page::class, 3)
            ->create(['template_id' => $mainTemplate->id])
            ->each(function ($item) use ($mainParentPage) {
                $item['parent_ids'] = [$mainParentPage->id];
                $item->name = 'UPDATED';
            });
        $data = $pages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'name' => 'UPDATED'
                    ]
                ]
            ])
            ->assertJsonFragment([
                'id' => $mainParentPage->id
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $pages->first()->id,
            'parent_id' => $mainParentPage->id
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $pages->last()->id,
            'parent_id' => $mainParentPage->id
        ]);
    }

    public function testUpdatePageByIdWithNoParentPageToSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        $page = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
        $page['parent_ids'] = [$mainParentPage->id];
        $page->name = 'UPDATED';
        $data = $page->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'UPDATED',
                    'parents' => [
                        [
                            'id' => $mainParentPage->id
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $page->id,
            'parent_id' => $mainParentPage->id
        ]);
    }

    //Update no -> no
    public function testUpdatePagesWithNoParentPageToNoParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);

        /** @var Page|Page[]|\Illuminate\Support\Collection $pages */
        $pages = factory(Page::class, 3)
            ->create(['template_id' => $mainTemplate->id])
            ->each(function ($item) {
                $item['parent_ids'] = [];
                $item->name = 'UPDATED';
            });
        $data = $pages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'name' => 'UPDATED',
                        'parents' => []
                    ]
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $pages->first()->id
        ]);
    }

    public function testUpdatePageByIdWithNoParentPageToNoParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);

        $page = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
        $page['parent_ids'] = [];
        $page->name = 'UPDATED';
        $data = $page->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'UPDATED',
                    'parents' => []
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $page->id
        ]);
    }

    //Update site parent page -> non site parent page
    public function testUpdatePagesWithSiteParentPageToNonSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        $differentPage = factory(Page::class)->create();

        /** @var Page|Page[]|\Illuminate\Support\Collection $pages */
        $pages = factory(Page::class, 3)
            ->create(['template_id' => $mainTemplate->id])
            ->each(function ($item) use ($mainParentPage, $differentPage) {
                /** @var Page $item */
                $item->parents()->attach($mainParentPage->id);
                $item['parent_ids'] = [$differentPage->id];
                $item->name = 'UPDATED';
            });
        $data = $pages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $pages->first()->id,
            'parent_id' => $mainParentPage->id
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $pages->first()->id,
            'parent_id' => $differentPage->id
        ]);
    }

    public function testUpdatePageByIdWithSiteParentPageToNonSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        $differentPage = factory(Page::class)->create();

        $page = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
        $page->parents()->attach($mainParentPage->id);
        $page['parent_ids'] = [$differentPage->id];
        $page->name = 'UPDATED';
        $data = $page->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $page->id,
            'parent_id' => $mainParentPage->id
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $page->id,
            'parent_id' => $differentPage->id
        ]);
    }

    //Update site parent page -> new parent page
    public function testUpdatePagesWithSiteParentPageToNewSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        $newParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        /** @var Page|Page[]|\Illuminate\Support\Collection $pages */
        $pages = factory(Page::class, 3)
            ->create(['template_id' => $mainTemplate->id])
            ->each(function ($item) use ($mainParentPage, $newParentPage) {
                /** @var Page $item */
                $item->parents()->attach($mainParentPage->id);
                $item['parent_ids'] = [$newParentPage->id];
                $item->name = 'UPDATED';
            });
        $data = $pages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'name' => 'UPDATED'
                    ]
                ]
            ])
            ->assertJsonFragment([
                'id' => $newParentPage->id
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $pages->first()->id,
            'parent_id' => $newParentPage->id
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $pages->first()->id,
            'parent_id' => $mainParentPage->id
        ]);
    }

    public function testUpdatePageByIdWithSiteParentPageToNewSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        $newParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        $page = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
        $page->parents()->attach($mainParentPage->id);
        $page['parent_ids'] = [$newParentPage->id];
        $page->name = 'UPDATED';
        $data = $page->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'UPDATED',
                    'parents' => [
                        [
                            'id' => $newParentPage->id
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $page->id,
            'parent_id' => $newParentPage->id
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $page->id,
            'parent_id' => $mainParentPage->id
        ]);
    }

    //Update site parent page -> same site parent page
    public function testUpdatePagesWithSiteParentPageToSameSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        /** @var Page|Page[]|\Illuminate\Support\Collection $pages */
        $pages = factory(Page::class, 3)
            ->create(['template_id' => $mainTemplate->id])
            ->each(function ($item) use ($mainParentPage) {
                /** @var Page $item */
                $item->parents()->attach($mainParentPage->id);
                $item['parent_ids'] = [$mainParentPage->id];
                $item->name = 'UPDATED';
            });
        $data = $pages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'name' => 'UPDATED'
                    ]
                ]
            ])
            ->assertJsonFragment([
                'id' => $mainParentPage->id
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $pages->first()->id,
            'parent_id' => $mainParentPage->id
        ]);
    }

    public function testUpdatePageByIdWithSiteParentPageToSameSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        $page = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
        $page->parents()->attach($mainParentPage->id);
        $page['parent_ids'] = [$mainParentPage->id];
        $page->name = 'UPDATED';
        $data = $page->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'UPDATED',
                    'parents' => [
                        [
                            'id' => $mainParentPage->id
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseHas('parent_page_mappings', [
            'page_id' => $page->id,
            'parent_id' => $mainParentPage->id
        ]);
    }

    //Update site parent page -> no
    public function testUpdatePagesWithSiteParentPageToNoSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        /** @var Page|Page[]|\Illuminate\Support\Collection $pages */
        $pages = factory(Page::class, 3)
            ->create(['template_id' => $mainTemplate->id])
            ->each(function ($item) use ($mainParentPage) {
                /** @var Page $item */
                $item->parents()->attach($mainParentPage->id);
                $item['parent_ids'] = [];
                $item->name = 'UPDATED';
            });
        $data = $pages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'name' => 'UPDATED',
                        'parents' => []
                    ]
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $pages->first()->id,
            'parent_id' => $mainParentPage->id
        ]);
    }

    public function testUpdatePageByIdWithSiteParentPageToNoSiteParentPage()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);

        $page = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
        $page->parents()->attach($mainParentPage->id);
        $page['parent_ids'] = [];
        $page->name = 'UPDATED';
        $data = $page->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $page->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'UPDATED',
                    'parents' => []
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'name' => 'UPDATED'
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $page->id,
            'parent_id' => $mainParentPage->id
        ]);
    }

    //Delete
    public function testDeletePagesWithSiteParentPagesCascade()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPages = factory(Page::class, 2)->create(['template_id' => $mainTemplate->id]);

        /** @var Page|Page[]|\Illuminate\Support\Collection $pages */
        $pages = factory(Page::class, 3)
            ->create(['template_id' => $mainTemplate->id])
            ->each(function ($item) use ($mainParentPages) {
                /** @var Page $item */
                $item->parents()->attach($mainParentPages->pluck('id')->toArray());
            });
        $data = $pages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/pages', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $pages->first()->id,
            'parent_id' => $mainParentPages->first()->id
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $pages->first()->id,
            'parent_id' => $mainParentPages->last()->id
        ]);
    }

    public function testDeletePageByIdWithSiteParentPagesCascade()
    {
        $mainSite = factory(Site::class)->create();
        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
        $mainParentPages = factory(Page::class, 2)->create(['template_id' => $mainTemplate->id]);

        $page = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
        $page->parents()->attach($mainParentPages->pluck('id')->toArray());

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/page/' . $page->id, [], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $page->id,
            'parent_id' => $mainParentPages->first()->id
        ]);

        $this->assertDatabaseMissing('parent_page_mappings', [
            'page_id' => $page->id,
            'parent_id' => $mainParentPages->last()->id
        ]);
    }
    
    //Categories
    //Create without categories
    public function testCreatePageWithoutCategories()
    {
        $site = factory(Site::class)->create();
        $template = factory(Template::class)->create(['site_id' => $site->id]);
        $params = [
            'name' => 'MOCK-UP PAGE',
            'variable_name' => self::randomVariableName(),
            'friendly_url' => self::$faker->slug,
            'description' => self::$faker->sentence(),
            'template_id' => $template->id,
            'categories' => null
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'template_id' => $template->id,
                    'name' => 'MOCK-UP PAGE',
                    'categories' => []
                ]
            ]);

        $this->assertDatabaseHas('pages', [
            'template_id' => $template->id,
            'name' => 'MOCK-UP PAGE',
        ]);
    }

    //Create with categories
    public function testCreatePageWithCategories()
    {
        $site = factory(Site::class)->create();
        $template = factory(Template::class)->create(['site_id' => $site->id]);
        $params = [
            'name' => 'MOCK-UP PAGE',
            'variable_name' => self::randomVariableName(),
            'friendly_url' => self::$faker->slug,
            'description' => self::$faker->sentence(),
            'template_id' => $template->id,
            'categories' => ['gallery', 'gallery2']
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'template_id' => $template->id,
                    'name' => 'MOCK-UP PAGE',
                    'categories' => []
                ]
            ])
            ->assertJsonFragment([
                'categories' => [strtoupper($params['categories'][0]), strtoupper($params['categories'][1])]
            ]);

        $this->assertDatabaseHas('pages', [
            'template_id' => $template->id,
            'name' => 'MOCK-UP PAGE'
        ]);

        $this->assertDatabaseHas('category_names', [
            'name' => strtoupper($params['categories'][0])
        ]);

        $this->assertDatabaseHas('category_names', [
            'name' => strtoupper($params['categories'][1])
        ]);
    }

    //Update from null to categories
    public function testUpdatePagesFromNullToCategories()
    {
        $items = factory(Page::class, 3)->create();
        $data = $items->each(function ($item, $key) {
            if ($key == 0) {
                $item['categories'] = ['GALLERY'];
            } else {
                $item['categories'] = ['GALLERY_ITEM'];
            }
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'categories' => ['GALLERY']
            ])
            ->assertJsonFragment([
                'categories' => ['GALLERY_ITEM']
            ]);

        $this->assertDatabaseHas('category_names', [
            'name' => 'GALLERY'
        ]);

        $this->assertDatabaseHas('category_names', [
            'name' => 'GALLERY_ITEM'
        ]);
    }

    public function testUpdatePageByIdFromNullToCategories()
    {
        $item = factory(Page::class)->create();
        $item['categories'] = ['GALLERY'];
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'categories' => ['GALLERY']
            ]);

        $this->assertDatabaseHas('category_names', [
            'name' => 'GALLERY'
        ]);
    }

    //Update from categories to new categories
    public function testUpdatePagesFromCategoriesToNewCategories()
    {
        /** @var Page[]|\Illuminate\Support\Collection $items */
        $items = factory(Page::class, 3)
            ->create()
            ->each(function ($item) {
                /** @var Page $item */
                $item->upsertOptionCategoryNames('gallery');
            });

        $data = $items->each(function ($item) {
            $item['categories'] = ['GALLERY_ITEM'];
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'categories' => ['GALLERY_ITEM']
            ]);

        $this->assertDatabaseHas('category_names', [
            'name' => 'GALLERY'
        ]);

        $this->assertDatabaseHas('category_names', [
            'name' => 'GALLERY_ITEM'
        ]);
    }

    public function testUpdatePageByIdFromCategoriesToNewCategories()
    {
        $item = factory(Page::class)->create();
        $item->upsertOptionCategoryNames('gallery');
        $item['categories'] = ['GALLERY_ITEM'];
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'categories' => ['GALLERY_ITEM']
            ]);

        $this->assertDatabaseHas('category_names', [
            'name' => 'GALLERY'
        ]);

        $this->assertDatabaseHas('category_names', [
            'name' => 'GALLERY_ITEM'
        ]);
    }

    //Update from categories to null
    public function testUpdatePagesFromCategoriesToNull()
    {
        /** @var Page[]|\Illuminate\Support\Collection $items */
        $items = factory(Page::class, 3)
            ->create()
            ->each(function ($item) {
                /** @var Page $item */
                $item->upsertOptionCategoryNames('gallery');
            });

        $data = $items->each(function ($item) {
            $item['categories'] = null;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'categories' => []
            ]);

        $this->assertDatabaseHas('category_names', [
            'name' => 'GALLERY'
        ]);
    }

    public function testUpdatePageByIdFromCategoriesToNull()
    {
        $item = factory(Page::class)->create();
        $item->upsertOptionCategoryNames('gallery');
        $item['categories'] = null;
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/page/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'categories' => []
            ]);

        $this->assertDatabaseHas('category_names', [
            'name' => 'GALLERY'
        ]);
    }
}
