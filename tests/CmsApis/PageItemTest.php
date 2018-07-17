<?php

namespace Tests\CmsApis;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\Component;
use App\Api\Models\ComponentOption;
use App\Api\Models\GlobalItem;
use App\Api\Models\Language;
use App\Api\Models\Page;
use App\Api\Models\PageItem;
use App\Api\Models\PageItemOption;
use App\Api\Models\Site;
use App\Api\Models\Template;
use Tests\CmsApiTestCase;

class PageItemTest extends CmsApiTestCase 
{
    /**
     * @var Language
     */
    private $english;

    /**
     * @var Language
     */
    private $thai;

    /**
     * @var Page
     */
    private $page;

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->english = Language::firstOrCreate([
            'code' => 'en',
            'name' => 'English'
        ]);

        $this->thai = Language::firstOrCreate([
            'code' => 'th',
            'name' => 'Thai'
        ]);

        $site = factory(Site::class)->create();
        $site->languages()->save($this->english, ['is_main' => true]);
        $site->languages()->save($this->thai);

        $template = factory(Template::class)->create(['site_id' => $site->id]);
        $this->page = factory(Page::class)->create(['template_id' => $template->id]);
    }

    public function testGetAllPageItems()
    {
        factory(PageItem::class, 3)->create();
        $items = PageItem::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/pageItems', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $items
            ]);
    }

    public function testGetPageItemById()
    {
        $item = factory(PageItem::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/pageItem/' . $item->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'id' => $item->id
                ]
            ]);
    }

    public function testStoreWithoutComponent()
    {
        $page = factory(Page::class)->create();
        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => self::randomVariableName(),
            'page_id' => $page->id,
            'is_active' => false
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'page_id' => $page->id,
                    'name' => 'MOCK-UP PAGE ITEM',
                    'display_order' => 1,
                    'is_active' => false
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'page_id' => $page->id,
            'name' => 'MOCK-UP PAGE ITEM',
            'is_active' => false
        ]);
    }

    public function testStoreWithComponent()
    {
        $component = factory(Component::class)->create();
        $page = factory(Page::class)->create();
        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => self::randomVariableName(),
            'page_id' => $page->id,
            'is_active' => false,
            'component_id' => $component->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'page_id' => $page->id,
                    'component_id' => $component->id,
                    'name' => 'MOCK-UP PAGE ITEM',
                    'display_order' => 1,
                    'is_active' => false
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'page_id' => $page->id,
            'component_id' => $component->id,
            'name' => 'MOCK-UP PAGE ITEM',
            'is_active' => false
        ]);
    }

    public function testStoreWithGlobalItem()
    {
        $site = factory(Site::class)->create();
        $globalItem = factory(GlobalItem::class)->create(['site_id' => $site->getKey()]);
        $template = factory(Template::class)->create(['site_id' => $site->getkey()]);
        $page = factory(Page::class)->create(['template_id' => $template->getKey()]);
        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => self::randomVariableName(),
            'page_id' => $page->id,
            'is_active' => false,
            'global_item_id' => $globalItem->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'page_id' => $page->id,
                    'global_item_id' => $globalItem->id,
                    'name' => 'MOCK-UP PAGE ITEM',
                    'display_order' => 1,
                    'is_active' => false
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'page_id' => $page->id,
            'global_item_id' => $globalItem->id,
            'name' => 'MOCK-UP PAGE ITEM',
            'is_active' => false
        ]);
    }

    public function testStoreDuplicateVariableNameError()
    {
        $page = factory(Page::class)->create();
        $item = factory(PageItem::class)->create([
            'page_id' => $page->id
        ]);
        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => $item->variable_name,
            'page_id' => $page->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('page_items', [
            'name' => 'MOCK-UP PAGE ITEM'
        ]);
    }

    public function testStoreErrorWithoutVariableNameAndComponentId()
    {
        $page = factory(Page::class)->create();
        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'page_id' => $page->id,
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('page_items', [
            'name' => 'MOCK-UP PAGE ITEM'
        ]);
    }

    public function testStoreErrorDifferentSiteGlobalItem()
    {
        $globalItem = factory(GlobalItem::class)->create();
        $page = factory(Page::class)->create();
        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => self::randomVariableName(),
            'page_id' => $page->id,
            'is_active' => false,
            'global_item_id' => $globalItem->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('page_items', [
            'name' => 'MOCK-UP PAGE ITEM'
        ]);
    }

    public function testUpdateNewComponent()
    {
        $component = factory(Component::class)->create();
        $items = factory(PageItem::class, 3)->create();
        $data = $items->each(function ($item) use ($component) {
            $item->component_id = $component->id;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'component_id' => null
                    ]
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'component_id' => null
        ]);
    }

    public function testUpdateNewPage()
    {
        $page = factory(Page::class)->create();
        $items = factory(PageItem::class, 3)->create();
        $data = $items->each(function ($item) use ($page) {
            $item->page_id = $page->id;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'page_id' => $page->id,
                        'page' => [
                            'id' => $page->id
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'page_id' => $page->id
        ]);
    }

    public function testUpdateNewGlobalItem()
    {
        $site = factory(Site::class)->create();
        $globalItem = factory(GlobalItem::class)->create(['site_id' => $site->getKey()]);
        $template = factory(Template::class)->create(['site_id' => $site->getkey()]);
        $page = factory(Page::class)->create(['template_id' => $template->getKey()]);
        $items = factory(PageItem::class, 3)->create(['page_id' => $page]);
        $data = $items->each(function ($item) use ($globalItem) {
            $item->global_item_id = $globalItem->id;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'global_item_id' => $globalItem->id,
                        'global_item' => [
                            'id' => $globalItem->id
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'global_item_id' => $globalItem->id
        ]);
    }

    public function testUpdateNewVariableName()
    {
        $items = factory(PageItem::class, 3)->create([
            'variable_name' => 'new_variable_name'
        ]);
        $data = $items->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'variable_name' => 'new_variable_name'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'variable_name' => 'new_variable_name'
        ]);
    }

    public function testUpdateNewDuplicateVariableNameErrorInTheSameParentPage()
    {
        $page = factory(Page::class)->create();
        $items = factory(PageItem::class, 3)->create([
            'page_id' => $page->id,
        ]);
        $data = $items->each(function ($item) use ($page) {
            $item->variable_name = 'new_variable_name';
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('page_items', [
            'variable_name' => 'new_variable_name'
        ]);
    }

    public function testUpdateByIdNewComponent()
    {
        $component = factory(Component::class)->create();
        $item = factory(PageItem::class)->create();
        $item->component_id = $component->id;
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'component_id' => null
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'component_id' => null
        ]);
    }

    public function testUpdateByIdNewPage()
    {
        $page = factory(Page::class)->create();
        $item = factory(PageItem::class)->create();
        $item->page_id = $page->id;
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'page_id' => $page->id,
                    'page' => [
                        'id' => $page->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'page_id' => $page->id
        ]);
    }

    public function testUpdateByIdNewGlobalItem()
    {
        $site = factory(Site::class)->create();
        $globalItem = factory(GlobalItem::class)->create(['site_id' => $site->getKey()]);
        $template = factory(Template::class)->create(['site_id' => $site->getkey()]);
        $page = factory(Page::class)->create(['template_id' => $template->getKey()]);
        $item = factory(PageItem::class)->create(['page_id' => $page->getKey()]);
        $item->global_item_id = $globalItem->id;
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'global_item_id' => $globalItem->id,
                    'global_item' => [
                        'id' => $globalItem->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'global_item_id' => $globalItem->id
        ]);
    }

    public function testUpdateByIdNewVariableName()
    {
        $item = factory(PageItem::class)->create([
            'variable_name' => 'new_variable_name'
        ]);
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'variable_name' => 'new_variable_name'
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'variable_name' => 'new_variable_name'
        ]);
    }

    public function testUpdateByIdNewDuplicateVariableNameErrorInTheSameParentPage()
    {
        $page = factory(Page::class)->create();
        factory(PageItem::class)->create([
            'variable_name' => 'new_variable_name',
            'page_id' => $page->id
        ]);
        $item = factory(PageItem::class)->create([
            'page_id' => $page->id,
        ]);
        $item->variable_name = 'new_variable_name';
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem/' . $item->id . '/update', ['data' => $data], $header);
        $response
            ->assertJson([
                'result' => false
            ]);
    }

    public function testDelete()
    {
        $items = factory(PageItem::class, 3)->create();
        $data = $items->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/pageItems', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('page_items', [
            'id' => $items->first()->id
        ]);
    }

    public function testDeleteById()
    {
        $item = factory(PageItem::class)->create();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/pageItem/' . $item->id, [], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('page_items', [
            'id' => $item->id
        ]);
    }

    //Display order
    //Same parent page
    public function testGetDisplayOrderSamePages()
    {
        $page = factory(Page::class)->create();
        $items = factory(PageItem::class, 3)->create([
            'page_id' => $page->id
        ])->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/pageItems', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'id' => $items[0]['id'],
                        'display_order' => 1
                    ],
                    [
                        'id' => $items[1]['id'],
                        'display_order' => 2
                    ],
                    [
                        'id' => $items[2]['id'],
                        'display_order' => 3
                    ]
                ]
            ]);
    }

    //order > max
    public function testCreateDisplayOrderSamePageWhereOrderIsMoreThanMaxOrder()
    {
        $page = factory(Page::class)->create();
        $items = factory(PageItem::class, 3)->create(['page_id' => $page->id]);
        $maxOrder = $items->last()->display_order;

        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => self::randomVariableName(),
            'page_id' => $page->id,
            'display_order' => $maxOrder + 10
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP PAGE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'MOCK-UP PAGE ITEM',
            'display_order' => $maxOrder + 1
        ]);
    }

    //order == max
    public function testCreateDisplayOrderSamePageWhereOrderIsEqualMaxOrder()
    {
        $page = factory(Page::class)->create();
        $items = factory(PageItem::class, 3)->create(['page_id' => $page->id]);
        $maxOrder = $items->last()->display_order;

        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => self::randomVariableName(),
            'page_id' => $page->id,
            'display_order' => $maxOrder
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP PAGE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'MOCK-UP PAGE ITEM',
            'display_order' => $maxOrder + 1
        ]);

    }

    //order < max
    public function testCreateDisplayOrderSamePageWhereOrderIsLessThanMaxOrder()
    {
        $page = factory(Page::class)->create();
        $items = factory(PageItem::class, 3)->create(['page_id' => $page->id]);
        $maxOrder = $items->last()->display_order;

        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => self::randomVariableName(),
            'page_id' => $page->id,
            'display_order' => $maxOrder - 1
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP PAGE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'MOCK-UP PAGE ITEM',
            'display_order' => $maxOrder + 1
        ]);
    }

    //order < min
    public function testCreateDisplayOrderSamePageWhereOrderIsLessThanMinOrder()
    {
        $page = factory(Page::class)->create();
        $items = factory(PageItem::class, 3)->create(['page_id' => $page->id]);
        $maxOrder = $items->last()->display_order;
        $minOrder = $items->first()->display_order;

        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => self::randomVariableName(),
            'page_id' => $page->id,
            'display_order' => $minOrder - 1
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP PAGE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'MOCK-UP PAGE ITEM',
            'display_order' => $maxOrder + 1
        ]);
    }

    //order == null
    public function testCreateDisplayOrderSamePageWhereOrderIsNull()
    {
        $page = factory(Page::class)->create();
        $items = factory(PageItem::class, 3)->create(['page_id' => $page->id]);
        $maxOrder = $items->last()->display_order;

        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => self::randomVariableName(),
            'page_id' => $page->id,
            'display_order' => null
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP PAGE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'MOCK-UP PAGE ITEM',
            'display_order' => $maxOrder + 1
        ]);
    }

    //order is not present
    public function testCreateDisplayOrderSamePageWhereOrderIsNotPresent()
    {
        $page = factory(Page::class)->create();
        $items = factory(PageItem::class, 3)->create(['page_id' => $page->id]);
        $maxOrder = $items->last()->display_order;

        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => self::randomVariableName(),
            'page_id' => $page->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP PAGE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'name' => 'MOCK-UP PAGE ITEM',
            'display_order' => $maxOrder + 1
        ]);
    }

    //Different parent page
    public function testGetDisplayOrderDifferentPages()
    {
        $items = factory(PageItem::class, 3)->create()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/pageItems', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'id' => $items[0]['id'],
                        'display_order' => 1
                    ],
                    [
                        'id' => $items[1]['id'],
                        'display_order' => 1
                    ],
                    [
                        'id' => $items[2]['id'],
                        'display_order' => 1
                    ]
                ]
            ]);
    }

    //Integrations
    public function testGetAllPageOptionsByPageItemId()
    {
        $pageItem = factory(PageItem::class)->create();

        /** @var PageItemOption|PageItemOption[] $pageItemOptions */
        $pageItemOptions = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->string()->create([
                'option_value' => 'TEST'
            ]);
        });

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/pageItem/' . $pageItem->id . '/pageItemOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'id' => $pageItemOptions->first()->id,
                        'option_type' => OptionValueConstants::STRING,
                        'option_value' => 'TEST'
                    ]
                ]
            ]);
    }

    //Categories
    //Create without categories
//    public function testCreatePageItemWithoutCategories()
//    {
//        $component = factory(Component::class)->create();
//        $page = factory(Page::class)->create();
//        $params = [
//            'name' => 'MOCK-UP PAGE ITEM',
//            'variable_name' => self::randomVariableName(),
//            'page_id' => $page->id,
//            'is_active' => false,
//            'component_id' => $component->id,
//            'categories' => null
//        ];
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem', $params, $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'page_id' => $page->id,
//                    'component_id' => $component->id,
//                    'name' => 'MOCK-UP PAGE ITEM',
//                    'display_order' => 1,
//                    'is_active' => false,
//                    'categories' => []
//                ]
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'page_id' => $page->id,
//            'component_id' => $component->id,
//            'name' => 'MOCK-UP PAGE ITEM',
//            'is_active' => false
//        ]);
//    }
//
//    //Create with categories
//    public function testCreatePageItemWithCategories()
//    {
//        $component = factory(Component::class)->create();
//        $page = factory(Page::class)->create();
//        $params = [
//            'name' => 'MOCK-UP PAGE ITEM',
//            'variable_name' => self::randomVariableName(),
//            'page_id' => $page->id,
//            'is_active' => false,
//            'component_id' => $component->id,
//            'categories' => ['gallery', 'gallery2']
//        ];
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem', $params, $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'page_id' => $page->id,
//                    'component_id' => $component->id,
//                    'name' => 'MOCK-UP PAGE ITEM',
//                    'display_order' => 1,
//                    'is_active' => false,
//                ]
//            ])
//            ->assertJsonFragment([
//                'categories' => [strtoupper($params['categories'][0]), strtoupper($params['categories'][1])]
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'page_id' => $page->id,
//            'component_id' => $component->id,
//            'name' => 'MOCK-UP PAGE ITEM',
//            'is_active' => false
//        ]);
//
//        $this->assertDatabaseHas('category_names', [
//            'name' => strtoupper($params['categories'][0])
//        ]);
//
//        $this->assertDatabaseHas('category_names', [
//            'name' => strtoupper($params['categories'][1])
//        ]);
//    }
//
//    //Update from null to categories
//    public function testUpdatePageItemsFromNullToCategories()
//    {
//        $items = factory(PageItem::class, 3)->create();
//        $data = $items->each(function ($item, $key) {
//            if ($key == 0) {
//                $item['categories'] = ['GALLERY'];
//            } else {
//                $item['categories'] = ['GALLERY_ITEM'];
//            }
//        })->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true
//            ])
//            ->assertJsonFragment([
//                'categories' => ['GALLERY']
//            ])
//            ->assertJsonFragment([
//                'categories' => ['GALLERY_ITEM']
//            ]);
//
//        $this->assertDatabaseHas('category_names', [
//            'name' => 'GALLERY'
//        ]);
//
//        $this->assertDatabaseHas('category_names', [
//            'name' => 'GALLERY_ITEM'
//        ]);
//    }
//
//    public function testUpdatePageItemByIdFromNullToCategories()
//    {
//        $item = factory(PageItem::class)->create();
//        $item['categories'] = ['GALLERY'];
//        $data = $item->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem/' . $item->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true
//            ])
//            ->assertJsonFragment([
//                'categories' => ['GALLERY']
//            ]);
//
//        $this->assertDatabaseHas('category_names', [
//            'name' => 'GALLERY'
//        ]);
//    }
//
//    //Update from categories to new categories
//    public function testUpdatePageItemsFromCategoriesToNewCategories()
//    {
//        /** @var PageItem[]|\Illuminate\Support\Collection $items */
//        $items = factory(PageItem::class, 3)
//            ->create()
//            ->each(function ($item) {
//                /** @var PageItem $item */
//                $item->upsertOptionCategoryNames('gallery');
//            });
//
//        $data = $items->each(function ($item) {
//            $item['categories'] = ['GALLERY_ITEM'];
//        })->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true
//            ])
//            ->assertJsonFragment([
//                'categories' => ['GALLERY_ITEM']
//            ]);
//
//        $this->assertDatabaseHas('category_names', [
//            'name' => 'GALLERY'
//        ]);
//
//        $this->assertDatabaseHas('category_names', [
//            'name' => 'GALLERY_ITEM'
//        ]);
//    }
//
//    public function testUpdatePageItemByIdFromCategoriesToNewCategories()
//    {
//        $item = factory(PageItem::class)->create();
//        $item->upsertOptionCategoryNames('gallery');
//        $item['categories'] = ['GALLERY_ITEM'];
//        $data = $item->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem/' . $item->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true
//            ])
//            ->assertJsonFragment([
//                'categories' => ['GALLERY_ITEM']
//            ]);
//
//        $this->assertDatabaseHas('category_names', [
//            'name' => 'GALLERY'
//        ]);
//
//        $this->assertDatabaseHas('category_names', [
//            'name' => 'GALLERY_ITEM'
//        ]);
//    }
//
//    //Update from categories to null
//    public function testUpdatePageItemsFromCategoriesToNull()
//    {
//        /** @var PageItem[]|\Illuminate\Support\Collection $items */
//        $items = factory(PageItem::class, 3)
//            ->create()
//            ->each(function ($item) {
//                /** @var PageItem $item */
//                $item->upsertOptionCategoryNames('gallery');
//            });
//
//        $data = $items->each(function ($item) {
//            $item['categories'] = null;
//        })->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true
//            ])
//            ->assertJsonFragment([
//                'categories' => []
//            ]);
//
//        $this->assertDatabaseHas('category_names', [
//            'name' => 'GALLERY'
//        ]);
//    }
//
//    public function testUpdatePageItemByIdFromCategoriesToNull()
//    {
//        $item = factory(PageItem::class)->create();
//        $item->upsertOptionCategoryNames('gallery');
//        $item['categories'] = null;
//        $data = $item->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem/' . $item->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true
//            ])
//            ->assertJsonFragment([
//                'categories' => []
//            ]);
//
//        $this->assertDatabaseHas('category_names', [
//            'name' => 'GALLERY'
//        ]);
//    }

    //Inherit
    public function testStoreWithComponentInheritsComponentOptions()
    {
        $component = factory(Component::class)->create();
        factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $params = [
            'name' => 'MOCK-UP PAGE ITEM',
            'variable_name' => self::randomVariableName(),
            'page_id' => $this->page->id,
            'is_active' => false,
            'component_id' => $component->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItem', $params, $header);

        /** @var \Dingo\Api\Http\Response|\Illuminate\Foundation\Testing\TestResponse $response */
        $content = json_decode($response->getContent());
        $data = $content->data;

        /** @var PageItem $pageItem */
        $pageItem = PageItem::findOrFail($data->id);
        $options = $pageItem->pageItemOptions;

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'page_id' => $this->page->id,
                    'component_id' => $component->id,
                    'name' => 'MOCK-UP PAGE ITEM'
                ]
            ]);

        $this->assertDatabaseHas('page_items', [
            'page_id' => $this->page->id,
            'component_id' => $component->id,
            'name' => 'MOCK-UP PAGE ITEM'
        ]);

        //Option Value
        $this->assertDatabaseHas('page_item_option_strings', [
            'page_item_option_id' => $options->first()->id,
            'option_value' => 'TEST'
        ]);

        //Element Type
        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);

        //Site Translation
        $this->assertDatabaseHas('site_translations', [
            'item_id' => $options->first()->id,
            'item_type' => class_basename(new PageItemOption),
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'item_id' => $options->first()->id,
            'item_type' => class_basename(new PageItemOption),
            'translated_text' => 'เทส'
        ]);
    }
    
    //Parent
    //Create with non-site parent page item
//    public function testCreatePageItemWithNonSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//
//        $differentSite = factory(Site::class)->create();
//        $differentTemplate = factory(Template::class)->create(['site_id' => $differentSite->id]);
//        $differentPage = factory(Page::class)->create(['template_id' => $differentTemplate->id]);
//        $differentParentPageItem = factory(PageItem::class)->create(['page_id' => $differentPage->id]);
//
//        $params = [
//            'name' => 'MOCK-UP PAGE ITEM',
//            'variable_name' => self::randomVariableName(),
//            'friendly_url' => self::$faker->slug,
//            'description' => self::$faker->sentence(),
//            'page_id' => $mainPage->id,
//            'parent_id' => $differentParentPageItem->id
//        ];
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem', $params, $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'name' => $params['name'],
//            'friendly_url' => $params['friendly_url'],
//            'template_id' => $mainTemplate->id,
//            'parent_id' => $differentParentPageItem->id
//        ]);
//    }
//
//    //Create with site parent page item
//    public function testCreatePageItemWithSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $params = [
//            'name' => 'MOCK-UP PAGE ITEM',
//            'variable_name' => self::randomVariableName(),
//            'friendly_url' => self::$faker->slug,
//            'description' => self::$faker->sentence(),
//            'page_id' => $mainPage->id,
//            'parent_id' => $mainParentPageItem->id
//        ];
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem', $params, $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'parent' => [
//                        'id' => $mainParentPageItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'name' => $params['name'],
//            'page_id' => $mainPage->id,
//            'parent_id' => $mainParentPageItem->id
//        ]);
//    }
//
//    //Update no -> non-site parent page item
//    public function testUpdatePageItemsWithNoParentPageItemToNonSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//
//        $differentSite = factory(Site::class)->create();
//        $differentTemplate = factory(Template::class)->create(['site_id' => $differentSite->id]);
//        $differentPage = factory(Page::class)->create(['template_id' => $differentTemplate->id]);
//        $differentParentPageItem = factory(PageItem::class)->create(['page_id' => $differentPage->id]);
//
//        $pageItems = factory(PageItem::class, 3)
//            ->create(['page_id' => $mainPage->id])
//            ->each(function ($item) use ($differentParentPageItem) {
//                $item['parent_id'] = $differentParentPageItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $pageItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'name' => 'UPDATED',
//            'parent_id' => $differentParentPageItem->id
//        ]);
//    }
//
//    public function testUpdatePageItemByIdWithNoParentPageItemToNonSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//
//        $differentSite = factory(Site::class)->create();
//        $differentTemplate = factory(Template::class)->create(['site_id' => $differentSite->id]);
//        $differentPage = factory(Page::class)->create(['template_id' => $differentTemplate->id]);
//        $differentParentPageItem = factory(PageItem::class)->create(['page_id' => $differentPage->id]);
//
//        $pageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//        $pageItem['parent_id'] = $differentParentPageItem->id;
//        $pageItem->name = 'UPDATED';
//        $data = $pageItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem/' . $pageItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'name' => 'UPDATED',
//            'parent_id' => $differentParentPageItem->id
//        ]);
//    }
//
//    //Update no -> site parent page item
//    public function testUpdatePageItemsWithNoParentPageItemToSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItems = factory(PageItem::class, 3)
//            ->create(['page_id' => $mainPage->id])
//            ->each(function ($item) use ($mainParentPageItem) {
//                $item['parent_id'] = $mainParentPageItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $pageItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    [
//                        'name' => 'UPDATED'
//                    ]
//                ]
//            ])
//            ->assertJsonFragment([
//                'id' => $mainParentPageItem->id
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentPageItem->id
//        ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItems->last()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentPageItem->id
//        ]);
//    }
//
//    public function testUpdatePageItemByIdWithNoParentPageItemToSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//        $pageItem['parent_id'] = $mainParentPageItem->id;
//        $pageItem->name = 'UPDATED';
//        $data = $pageItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem/' . $pageItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'name' => 'UPDATED',
//                    'parent' => [
//                        'id' => $mainParentPageItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentPageItem->id
//        ]);
//    }
//
//    //Update no -> no
//    public function testUpdatePageItemsWithNoParentPageItemToNoParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//
//        $pageItems = factory(PageItem::class, 3)
//            ->create(['page_id' => $mainPage->id])
//            ->each(function ($item) {
//                $item['parent_id'] = null;
//                $item->name = 'UPDATED';
//            });
//        $data = $pageItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    [
//                        'name' => 'UPDATED',
//                        'parent' => null
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => null
//        ]);
//    }
//
//    public function testUpdatePageItemByIdWithNoParentPageItemToNoParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//
//        $pageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//        $pageItem['parent_id'] = null;
//        $pageItem->name = 'UPDATED';
//        $data = $pageItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem/' . $pageItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'name' => 'UPDATED',
//                    'parent' => null
//                ]
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => null
//        ]);
//    }
//
//    //Update site parent page item -> non site parent page item
//    public function testUpdatePageItemsWithSiteParentPageItemToNonSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $differentPageItem = factory(PageItem::class)->create();
//
//        $pageItems = factory(PageItem::class, 3)
//            ->create(['page_id' => $mainPage->id])
//            ->each(function ($item) use ($mainParentPageItem, $differentPageItem) {
//                $item->parent()->associate($mainParentPageItem->id);
//                $item->save();
//
//                $item['parent_id'] = $differentPageItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $pageItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'id' => $pageItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $differentPageItem->id
//        ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItems->first()->id,
//            'parent_id' => $mainParentPageItem->id
//        ]);
//    }
//
//    public function testUpdatePageItemByIdWithSiteParentPageItemToNonSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $differentPageItem = factory(PageItem::class)->create();
//
//        $pageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//        $pageItem->parent()->associate($mainParentPageItem->id);
//        $pageItem->save();
//
//        $pageItem['parent_id'] = $differentPageItem->id;
//        $pageItem->name = 'UPDATED';
//        $data = $pageItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem/' . $pageItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'id' => $pageItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => $differentPageItem->id
//        ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItem->id,
//            'parent_id' => $mainParentPageItem->id
//        ]);
//    }
//
//    //Update site parent page item -> new parent page item
//    public function testUpdatePageItemsWithSiteParentPageItemToNewSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $newParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItems = factory(PageItem::class, 3)
//            ->create(['page_id' => $mainPage->id])
//            ->each(function ($item) use ($mainParentPageItem, $newParentPageItem) {
//                $item->parent()->associate($mainParentPageItem->id);
//                $item->save();
//
//                $item['parent_id'] = $newParentPageItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $pageItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    [
//                        'name' => 'UPDATED'
//                    ]
//                ]
//            ])
//            ->assertJsonFragment([
//                'id' => $newParentPageItem->id
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $newParentPageItem->id
//        ]);
//    }
//
//    public function testUpdatePageItemByIdWithSiteParentPageItemToNewSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $newParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//        $pageItem->parent()->associate($mainParentPageItem->id);
//        $pageItem->save();
//
//        $pageItem['parent_id'] = $newParentPageItem->id;
//        $pageItem->name = 'UPDATED';
//        $data = $pageItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem/' . $pageItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'name' => 'UPDATED',
//                    'parent' => [
//                        'id' => $newParentPageItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => $newParentPageItem->id
//        ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'id' => $pageItem->id,
//            'parent_id' => $mainParentPageItem->id
//        ]);
//    }
//
//    //Update site parent page item -> same site parent page item
//    public function testUpdatePageItemsWithSiteParentPageItemToSameSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItems = factory(PageItem::class, 3)
//            ->create(['page_id' => $mainPage->id])
//            ->each(function ($item) use ($mainParentPageItem) {
//                $item->parent()->associate($mainParentPageItem->id);
//                $item->save();
//
//                $item['parent_id'] = $mainParentPageItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $pageItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    [
//                        'name' => 'UPDATED'
//                    ]
//                ]
//            ])
//            ->assertJsonFragment([
//                'id' => $mainParentPageItem->id
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentPageItem->id
//        ]);
//    }
//
//    public function testUpdatePageItemByIdWithSiteParentPageItemToSameSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//        $pageItem->parent()->associate($mainParentPageItem->id);
//        $pageItem->save();
//
//        $pageItem['parent_id'] = $mainParentPageItem->id;
//        $pageItem->name = 'UPDATED';
//        $data = $pageItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem/' . $pageItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'name' => 'UPDATED',
//                    'parent' => [
//                        'id' => $mainParentPageItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentPageItem->id
//        ]);
//    }
//
//    //Update site parent page item -> no
//    public function testUpdatePageItemsWithSiteParentPageItemToNoSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItems = factory(PageItem::class, 3)
//            ->create(['page_id' => $mainPage->id])
//            ->each(function ($item) use ($mainParentPageItem) {
//                $item->parent()->associate($mainParentPageItem->id);
//                $item->save();
//
//                $item['parent_id'] = null;
//                $item->name = 'UPDATED';
//            });
//        $data = $pageItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    [
//                        'name' => 'UPDATED',
//                        'parent' => null
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItems->first()->id,
//            'name' => 'UPDATED'
//        ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'id' => $pageItems->first()->id,
//            'parent_id' => $mainParentPageItem->id
//        ]);
//    }
//
//    public function testUpdatePageItemByIdWithSiteParentPageItemToNoSiteParentPageItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//        $pageItem->parent()->associate($mainParentPageItem->id);
//        $pageItem->save();
//
//        $pageItem['parent_id'] = null;
//        $pageItem->name = 'UPDATED';
//        $data = $pageItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/pageItem/' . $pageItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'name' => 'UPDATED',
//                    'parent' => null
//                ]
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItem->id,
//            'name' => 'UPDATED'
//        ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'id' => $pageItem->id,
//            'parent_id' => $mainParentPageItem->id
//        ]);
//    }
//
//    //Delete
//    public function testDeleteParentPageItemsWithSiteParentPageItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItems = factory(PageItem::class, 3)
//            ->create(['page_id' => $mainPage->id])
//            ->each(function ($item) use ($mainParentPageItem) {
//                $item->parent()->associate($mainParentPageItem->id);
//                $item->save();
//            });
//        $data = [$mainParentPageItem->toArray()];
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/pageItems', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'id' => $mainParentPageItem
//        ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItems->first()->id,
//            'parent_id' => null
//        ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItems->last()->id,
//            'parent_id' => null
//        ]);
//    }
//
//    public function testDeleteParentPageItemByIdWithSiteParentPageItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//        $pageItem->parent()->associate($mainParentPageItem->id);
//        $pageItem->save();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/pageItem/' . $mainParentPageItem->id, [], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $pageItem->id,
//            'parent_id' => null
//        ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'id' => $mainParentPageItem->id
//        ]);
//    }
//
//    public function testDeletePageItemsWithSiteParentPageItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItems = factory(PageItem::class, 3)
//            ->create(['page_id' => $mainPage->id])
//            ->each(function ($item) use ($mainParentPageItem) {
//                $item->parent()->associate($mainParentPageItem->id);
//                $item->save();
//            });
//        $data = $pageItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/pageItems', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'id' => $pageItems->first()->id
//        ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'id' => $pageItems->last()->id
//        ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $mainParentPageItem->id
//        ]);
//    }
//
//    public function testDeletePageItemByIdWithSiteParentPageItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainPage = factory(Page::class)->create(['template_id' => $mainTemplate->id]);
//        $mainParentPageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//
//        $pageItem = factory(PageItem::class)->create(['page_id' => $mainPage->id]);
//        $pageItem->parent()->associate($mainParentPageItem->id);
//        $pageItem->save();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/pageItem/' . $pageItem->id, [], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseHas('page_items', [
//            'id' => $mainParentPageItem->id
//        ]);
//
//        $this->assertDatabaseMissing('page_items', [
//            'id' => $pageItem->id
//        ]);
//    }
}

