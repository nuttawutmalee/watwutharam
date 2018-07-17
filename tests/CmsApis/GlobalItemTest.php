<?php

namespace Tests\CmsApis;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\Component;
use App\Api\Models\ComponentOption;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\Language;
use App\Api\Models\Site;
use Tests\CmsApiTestCase;

class GlobalItemTest extends CmsApiTestCase
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
     * @var Site
     */
    private $site;

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

        $this->site = factory(Site::class)->create();
        $this->site->languages()->save($this->english, ['is_main' => true]);
        $this->site->languages()->save($this->thai);
    }

    public function testGetAllGlobalItems()
    {
        factory(GlobalItem::class, 3)->create();
        $items = GlobalItem::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/globalItems', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $items
            ]);
    }

    public function testGetGlobalItemById()
    {
        $item = factory(GlobalItem::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/globalItem/' . $item->id, self::$developerAuthorizationHeader);

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
        $site = factory(Site::class)->create();
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM',
            'variable_name' => self::randomVariableName(),
            'site_id' => $site->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'site_id' => $site->id,
                    'name' => 'MOCK-UP GLOBAL ITEM'
                ]
            ]);

        $this->assertDatabaseHas('global_items', [
            'site_id' => $site->id,
            'name' => 'MOCK-UP GLOBAL ITEM',
        ]);
    }

    public function testStoreWithComponent()
    {
        $component = factory(Component::class)->create();
        $site = factory(Site::class)->create();
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM',
            'variable_name' => self::randomVariableName(),
            'site_id' => $site->id,
            'component_id' => $component->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'site_id' => $site->id,
                    'component_id' => $component->id,
                    'name' => 'MOCK-UP GLOBAL ITEM'
                ]
            ]);

        $this->assertDatabaseHas('global_items', [
            'site_id' => $site->id,
            'component_id' => $component->id,
            'name' => 'MOCK-UP GLOBAL ITEM'
        ]);
    }

    public function testStoreDuplicateVariableNameError()
    {
        $site = factory(Site::class)->create();
        $item = factory(GlobalItem::class)->create([
            'site_id' => $site->id
        ]);
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM',
            'variable_name' => $item->variable_name,
            'site_id' => $site->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('global_items', [
            'name' => 'MOCK-UP GLOBAL ITEM'
        ]);
    }

    public function testStoreErrorWithoutVariableNameAndComponentId()
    {
        $site = factory(Site::class)->create();
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM',
            'site_id' => $site->id,
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('global_items', [
            'name' => 'MOCK-UP GLOBAL ITEM'
        ]);
    }

    public function testUpdateNewComponent()
    {
        $component = factory(Component::class)->create();
        $items = factory(GlobalItem::class, 3)->create();
        $data = $items->each(function ($item) use ($component) {
            $item->component_id = $component->id;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);

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

        $this->assertDatabaseHas('global_items', [
            'component_id' => null
        ]);
    }

    public function testUpdateNewSite()
    {
        $site = factory(Site::class)->create();
        $items = factory(GlobalItem::class, 3)->create();
        $data = $items->each(function ($item) use ($site) {
            $item->site_id = $site->id;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'site_id' => $site->id,
                        'site' => [
                            'id' => $site->id
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('global_items', [
            'site_id' => $site->id
        ]);
    }

    public function testUpdateNewVariableName()
    {
        $items = factory(GlobalItem::class, 3)->create([
            'variable_name' => 'new_variable_name'
        ]);
        $data = $items->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);

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

        $this->assertDatabaseHas('global_items', [
            'variable_name' => 'new_variable_name'
        ]);
    }

    public function testUpdateNewDuplicateVariableNameErrorInTheSameParentSite()
    {
        $site = factory(Site::class)->create();
        $items = factory(GlobalItem::class, 3)->create([
            'site_id' => $site->id,
        ]);
        $data = $items->each(function ($item) use ($site) {
            $item->variable_name = 'new_variable_name';
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('global_items', [
            'variable_name' => 'new_variable_name'
        ]);
    }

    public function testUpdateByIdNewComponent()
    {
        $component = factory(Component::class)->create();
        $item = factory(GlobalItem::class)->create();
        $item->component_id = $component->id;
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'component_id' => null
                ]
            ]);

        $this->assertDatabaseHas('global_items', [
            'component_id' => null
        ]);
    }

    public function testUpdateByIdNewSite()
    {
        $site = factory(Site::class)->create();
        $item = factory(GlobalItem::class)->create();
        $item->site_id = $site->id;
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'site_id' => $site->id,
                    'site' => [
                        'id' => $site->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('global_items', [
            'site_id' => $site->id
        ]);
    }

    public function testUpdateByIdNewVariableName()
    {
        $item = factory(GlobalItem::class)->create([
            'variable_name' => 'new_variable_name'
        ]);
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'variable_name' => 'new_variable_name'
                ]
            ]);

        $this->assertDatabaseHas('global_items', [
            'variable_name' => 'new_variable_name'
        ]);
    }

    public function testUpdateByIdNewDuplicateVariableNameErrorInTheSameParentSite()
    {
        $site = factory(Site::class)->create();
        factory(GlobalItem::class)->create([
            'variable_name' => 'new_variable_name',
            'site_id' => $site->id
        ]);
        $item = factory(GlobalItem::class)->create([
            'site_id' => $site->id,
        ]);
        $item->variable_name = 'new_variable_name';
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem/' . $item->id . '/update', ['data' => $data], $header);
        $response
            ->assertJson([
                'result' => false
            ]);
    }

    public function testDelete()
    {
        $items = factory(GlobalItem::class, 3)->create();
        $data = $items->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/globalItems', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('global_items', [
            'id' => $items->first()->id
        ]);
    }

    public function testDeleteById()
    {
        $item = factory(GlobalItem::class)->create();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/globalItem/' . $item->id, [], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('global_items', [
            'id' => $item->id
        ]);
    }
    
    //Integrations
    public function testGetAllSiteOptionsByGlobalItemId()
    {
        $globalItem = factory(GlobalItem::class)->create();

        /** @var GlobalItemOption|GlobalItemOption[] $globalItemOptions */
        $globalItemOptions = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->string()->create([
                'option_value' => 'TEST'
            ]);
        });

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/globalItem/' . $globalItem->id . '/globalItemOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'id' => $globalItemOptions->first()->id,
                        'option_type' => OptionValueConstants::STRING,
                        'option_value' => 'TEST'
                    ]
                ]
            ]);
    }

    //Categories
    //Create without categories
    public function testCreateGlobalItemWithoutCategories()
    {
        $component = factory(Component::class)->create();
        $site = factory(Site::class)->create();
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM',
            'variable_name' => self::randomVariableName(),
            'site_id' => $site->id,
            'is_active' => false,
            'component_id' => $component->id,
            'categories' => null
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'site_id' => $site->id,
                    'component_id' => $component->id,
                    'name' => 'MOCK-UP GLOBAL ITEM',
                    'is_active' => false,
                    'categories' => []
                ]
            ]);

        $this->assertDatabaseHas('global_items', [
            'site_id' => $site->id,
            'component_id' => $component->id,
            'name' => 'MOCK-UP GLOBAL ITEM',
            'is_active' => false
        ]);
    }

    //Create with categories
    public function testCreateGlobalItemWithCategories()
    {
        $component = factory(Component::class)->create();
        $site = factory(Site::class)->create();
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM',
            'variable_name' => self::randomVariableName(),
            'site_id' => $site->id,
            'is_active' => false,
            'component_id' => $component->id,
            'categories' => ['gallery', 'gallery2']
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'site_id' => $site->id,
                    'component_id' => $component->id,
                    'name' => 'MOCK-UP GLOBAL ITEM',
                    'is_active' => false
                ]
            ])
            ->assertJsonFragment([
                'categories' => [strtoupper($params['categories'][0]), strtoupper($params['categories'][1])]
            ]);

        $this->assertDatabaseHas('global_items', [
            'site_id' => $site->id,
            'component_id' => $component->id,
            'name' => 'MOCK-UP GLOBAL ITEM',
            'is_active' => false
        ]);

        $this->assertDatabaseHas('category_names', [
            'name' => strtoupper($params['categories'][0])
        ]);

        $this->assertDatabaseHas('category_names', [
            'name' => strtoupper($params['categories'][1])
        ]);
    }

    //Update from null to categories
    public function testUpdateGlobalItemsFromNullToCategories()
    {
        $items = factory(GlobalItem::class, 3)->create();
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
            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);

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

    public function testUpdateGlobalItemByIdFromNullToCategories()
    {
        $item = factory(GlobalItem::class)->create();
        $item['categories'] = ['GALLERY'];
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem/' . $item->id . '/update', ['data' => $data], $header);

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
    public function testUpdateGlobalItemsFromCategoriesToNewCategories()
    {
        /** @var GlobalItem[]|\Illuminate\Support\Collection $items */
        $items = factory(GlobalItem::class, 3)
            ->create()
            ->each(function ($item) {
                /** @var GlobalItem $item */
                $item->upsertOptionCategoryNames('gallery');
            });

        $data = $items->each(function ($item) {
            $item['categories'] = ['GALLERY_ITEM'];
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);

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

    public function testUpdateGlobalItemByIdFromCategoriesToNewCategories()
    {
        $item = factory(GlobalItem::class)->create();
        $item->upsertOptionCategoryNames('gallery');
        $item['categories'] = ['GALLERY_ITEM'];
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem/' . $item->id . '/update', ['data' => $data], $header);

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
    public function testUpdateGlobalItemsFromCategoriesToNull()
    {
        /** @var GlobalItem[]|\Illuminate\Support\Collection $items */
        $items = factory(GlobalItem::class, 3)
            ->create()
            ->each(function ($item) {
                /** @var GlobalItem $item */
                $item->upsertOptionCategoryNames('gallery');
            });

        $data = $items->each(function ($item) {
            $item['categories'] = null;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);

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

    public function testUpdateGlobalItemByIdFromCategoriesToNull()
    {
        $item = factory(GlobalItem::class)->create();
        $item->upsertOptionCategoryNames('gallery');
        $item['categories'] = null;
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem/' . $item->id . '/update', ['data' => $data], $header);

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
            'name' => 'MOCK-UP GLOBAL ITEM',
            'variable_name' => self::randomVariableName(),
            'site_id' => $this->site->id,
            'component_id' => $component->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        /** @var \Dingo\Api\Http\Response|\Illuminate\Foundation\Testing\TestResponse $response */
        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItem', $params, $header);

        $content = json_decode($response->getContent());
        $data = $content->data;

        /** @var GlobalItem $globalItem */
        $globalItem = GlobalItem::findOrFail($data->id);
        $options = $globalItem->globalItemOptions;

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'site_id' => $this->site->id,
                    'component_id' => $component->id,
                    'name' => 'MOCK-UP GLOBAL ITEM'
                ]
            ]);

        $this->assertDatabaseHas('global_items', [
            'site_id' => $this->site->id,
            'component_id' => $component->id,
            'name' => 'MOCK-UP GLOBAL ITEM'
        ]);

        //Option Value
        $this->assertDatabaseHas('global_item_option_strings', [
            'global_item_option_id' => $options->first()->id,
            'option_value' => 'TEST'
        ]);

        //Element Type
        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);

        //Site Translation
        $this->assertDatabaseHas('site_translations', [
            'item_id' => $options->first()->id,
            'item_type' => class_basename(new GlobalItemOption),
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'item_id' => $options->first()->id,
            'item_type' => class_basename(new GlobalItemOption),
            'translated_text' => 'เทส'
        ]);
    }

//    public function testReorderGlobalItemsByParentGlobalItemId()
//    {
//        $parent = factory(GlobalItem::class)->create(['site_id' => $this->site->id]);
//
//        /** @var GlobalItem|GlobalItem[] $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)->create([
//            'site_id' => $this->site->id,
//            'parent_id' => $parent->id
//        ])->each(function ($item, $key) {
//            /** @var GlobalItem $item */
//            $item->name = 'USED TO BE NUMBER ' . ($key + 2);
//            $item->save();
//        });
//
//        $globalItems->first()->display_order = 4;
//        $globalItems->last()->display_order = 2;
//
//        $data = $globalItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem/' . $parent->id . '/reorder', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->first()->id,
//            'name' => 'USED TO BE NUMBER 2',
//            'display_order' => 4
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->last()->id,
//            'name' => 'USED TO BE NUMBER 4',
//            'display_order' => 2
//        ]);
//    }
//
//    public function testReorderGlobalItemsByParentGlobalItemIdErrorWithTheSameOrder()
//    {
//        $parent = factory(GlobalItem::class)->create(['site_id' => $this->site->id]);
//
//        /** @var GlobalItem|GlobalItem[] $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)->create([
//            'site_id' => $this->site->id,
//            'parent_id' => $parent->id
//        ])->each(function ($item, $key) {
//            /** @var GlobalItem $item */
//            $item->name = 'USED TO BE NUMBER ' . ($key + 2);
//            $item->save();
//        });
//
//        $globalItems->first()->display_order = 1;
//        $globalItems->last()->display_order = 1;
//
//        $data = $globalItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem/' . $parent->id . '/reorder', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->first()->id,
//            'name' => 'USED TO BE NUMBER 2',
//            'display_order' => 2
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->last()->id,
//            'name' => 'USED TO BE NUMBER 4',
//            'display_order' => 4
//        ]);
//    }
//
//    public function testReorderGlobalItemsByParentGlobalItemIdErrorWithMissingOrder()
//    {
//        $parent = factory(GlobalItem::class)->create(['site_id' => $this->site->id]);
//
//        /** @var GlobalItem[]|\Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)->create([
//            'site_id' => $this->site->id,
//            'parent_id' => $parent->id
//        ])->each(function ($item, $key) {
//            /** @var GlobalItem $item */
//            $item->name = 'USED TO BE NUMBER ' . ($key + 2);
//            $item->save();
//        });
//
//        $data = $globalItems->map(function ($item) {
//            return collect($item)->except('display_order');
//        })->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem/' . $parent->id . '/reorder', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->first()->id,
//            'name' => 'USED TO BE NUMBER 2',
//            'display_order' => 2
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->last()->id,
//            'name' => 'USED TO BE NUMBER 4',
//            'display_order' => 4
//        ]);
//    }
//
//    //Parent
//    //Create with non-site parent global item
//    public function testCreateGlobalItemWithNonSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//
//        $differentSite = factory(Site::class)->create();
//        $differentParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $differentSite->id]);
//
//        $params = [
//            'name' => 'MOCK-UP GLOBAL ITEM',
//            'variable_name' => self::randomVariableName(),
//            'friendly_url' => self::$faker->slug,
//            'description' => self::$faker->sentence(),
//            'site_id' => $mainSite->id,
//            'parent_id' => $differentParentGlobalItem->id
//        ];
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem', $params, $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'name' => $params['name'],
//            'friendly_url' => $params['friendly_url'],
//            'template_id' => $mainSite->id,
//            'parent_id' => $differentParentGlobalItem->id
//        ]);
//    }
//
//    //Create with site parent global item
//    public function testCreateGlobalItemWithSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        $params = [
//            'name' => 'MOCK-UP GLOBAL ITEM',
//            'variable_name' => self::randomVariableName(),
//            'friendly_url' => self::$faker->slug,
//            'description' => self::$faker->sentence(),
//            'site_id' => $mainSite->id,
//            'parent_id' => $mainParentGlobalItem->id
//        ];
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem', $params, $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'parent' => [
//                        'id' => $mainParentGlobalItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'name' => $params['name'],
//            'site_id' => $mainSite->id,
//            'parent_id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    //Update no -> non-site parent global item
//    public function testUpdateGlobalItemsWithNoParentGlobalItemToNonSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//
//        $differentSite = factory(Site::class)->create();
//        $differentParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $differentSite->id]);
//
//        /** @var \Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)
//            ->create(['site_id' => $mainSite->id])
//            ->each(function ($item) use ($differentParentGlobalItem) {
//                $item['parent_id'] = $differentParentGlobalItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $globalItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'name' => 'UPDATED',
//            'parent_id' => $differentParentGlobalItem->id
//        ]);
//    }
//
//    public function testUpdateGlobalItemByIdWithNoParentGlobalItemToNonSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//
//        $differentSite = factory(Site::class)->create();
//        $differentParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $differentSite->id]);
//
//        $globalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//        $globalItem['parent_id'] = $differentParentGlobalItem->id;
//        $globalItem->name = 'UPDATED';
//        $data = $globalItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem/' . $globalItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'name' => 'UPDATED',
//            'parent_id' => $differentParentGlobalItem->id
//        ]);
//    }
//
//    //Update no -> site parent global item
//    public function testUpdateGlobalItemsWithNoParentGlobalItemToSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        /** @var \Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)
//            ->create(['site_id' => $mainSite->id])
//            ->each(function ($item) use ($mainParentGlobalItem) {
//                $item['parent_id'] = $mainParentGlobalItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $globalItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);
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
//                'id' => $mainParentGlobalItem->id
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentGlobalItem->id
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->last()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    public function testUpdateGlobalItemByIdWithNoParentGlobalItemToSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        $globalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//        $globalItem['parent_id'] = $mainParentGlobalItem->id;
//        $globalItem->name = 'UPDATED';
//        $data = $globalItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem/' . $globalItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'name' => 'UPDATED',
//                    'parent' => [
//                        'id' => $mainParentGlobalItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    //Update no -> no
//    public function testUpdateGlobalItemsWithNoParentGlobalItemToNoParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//
//        /** @var \Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)
//            ->create(['site_id' => $mainSite->id])
//            ->each(function ($item) {
//                $item['parent_id'] = null;
//                $item->name = 'UPDATED';
//            });
//        $data = $globalItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);
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
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => null
//        ]);
//    }
//
//    public function testUpdateGlobalItemByIdWithNoParentGlobalItemToNoParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//
//        $globalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//        $globalItem['parent_id'] = null;
//        $globalItem->name = 'UPDATED';
//        $data = $globalItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem/' . $globalItem->id . '/update', ['data' => $data], $header);
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
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => null
//        ]);
//    }
//
//    //Update site parent global item -> non site parent global item
//    public function testUpdateGlobalItemsWithSiteParentGlobalItemToNonSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        $differentGlobalItem = factory(GlobalItem::class)->create();
//
//        /** @var GlobalItem[]|\Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)
//            ->create(['site_id' => $mainSite->id])
//            ->each(function ($item) use ($mainParentGlobalItem, $differentGlobalItem) {
//                /** @var GlobalItem $item */
//                $item->parent()->associate($mainParentGlobalItem->id);
//                $item->save();
//
//                $item['parent_id'] = $differentGlobalItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $globalItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'id' => $globalItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $differentGlobalItem->id
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->first()->id,
//            'parent_id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    public function testUpdateGlobalItemByIdWithSiteParentGlobalItemToNonSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        $differentGlobalItem = factory(GlobalItem::class)->create();
//
//        $globalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//        $globalItem->parent()->associate($mainParentGlobalItem->id);
//        $globalItem->save();
//
//        $globalItem['parent_id'] = $differentGlobalItem->id;
//        $globalItem->name = 'UPDATED';
//        $data = $globalItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem/' . $globalItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'id' => $globalItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => $differentGlobalItem->id
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItem->id,
//            'parent_id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    //Update site parent global item -> new parent global item
//    public function testUpdateGlobalItemsWithSiteParentGlobalItemToNewSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        $newParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        /** @var GlobalItem[]|\Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)
//            ->create(['site_id' => $mainSite->id])
//            ->each(function ($item) use ($mainParentGlobalItem, $newParentGlobalItem) {
//                /** @var GlobalItem $item */
//                $item->parent()->associate($mainParentGlobalItem->id);
//                $item->save();
//
//                $item['parent_id'] = $newParentGlobalItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $globalItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);
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
//                'id' => $newParentGlobalItem->id
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $newParentGlobalItem->id
//        ]);
//    }
//
//    public function testUpdateGlobalItemByIdWithSiteParentGlobalItemToNewSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        $newParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        $globalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//        $globalItem->parent()->associate($mainParentGlobalItem->id);
//        $globalItem->save();
//
//        $globalItem['parent_id'] = $newParentGlobalItem->id;
//        $globalItem->name = 'UPDATED';
//        $data = $globalItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem/' . $globalItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'name' => 'UPDATED',
//                    'parent' => [
//                        'id' => $newParentGlobalItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => $newParentGlobalItem->id
//        ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'id' => $globalItem->id,
//            'parent_id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    //Update site parent global item -> same site parent global item
//    public function testUpdateGlobalItemsWithSiteParentGlobalItemToSameSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        /** @var GlobalItem[]|\Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)
//            ->create(['site_id' => $mainSite->id])
//            ->each(function ($item) use ($mainParentGlobalItem) {
//                /** @var GlobalItem $item */
//                $item->parent()->associate($mainParentGlobalItem->id);
//                $item->save();
//
//                $item['parent_id'] = $mainParentGlobalItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $globalItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);
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
//                'id' => $mainParentGlobalItem->id
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    public function testUpdateGlobalItemByIdWithSiteParentGlobalItemToSameSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        $globalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//        $globalItem->parent()->associate($mainParentGlobalItem->id);
//        $globalItem->save();
//
//        $globalItem['parent_id'] = $mainParentGlobalItem->id;
//        $globalItem->name = 'UPDATED';
//        $data = $globalItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem/' . $globalItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'name' => 'UPDATED',
//                    'parent' => [
//                        'id' => $mainParentGlobalItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    //Update site parent global item -> no
//    public function testUpdateGlobalItemsWithSiteParentGlobalItemToNoSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        /** @var GlobalItem[]|\Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)
//            ->create(['site_id' => $mainSite->id])
//            ->each(function ($item) use ($mainParentGlobalItem) {
//                /** @var GlobalItem $item */
//                $item->parent()->associate($mainParentGlobalItem->id);
//                $item->save();
//
//                $item['parent_id'] = null;
//                $item->name = 'UPDATED';
//            });
//        $data = $globalItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItems/update', ['data' => $data], $header);
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
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->first()->id,
//            'name' => 'UPDATED'
//        ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'id' => $globalItems->first()->id,
//            'parent_id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    public function testUpdateGlobalItemByIdWithSiteParentGlobalItemToNoSiteParentGlobalItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        $globalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//        $globalItem->parent()->associate($mainParentGlobalItem->id);
//        $globalItem->save();
//
//        $globalItem['parent_id'] = null;
//        $globalItem->name = 'UPDATED';
//        $data = $globalItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/globalItem/' . $globalItem->id . '/update', ['data' => $data], $header);
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
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItem->id,
//            'name' => 'UPDATED'
//        ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'id' => $globalItem->id,
//            'parent_id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    //Delete
//    public function testDeleteParentGlobalItemsWithSiteParentGlobalItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        /** @var GlobalItem[]|\Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)
//            ->create(['site_id' => $mainSite->id])
//            ->each(function ($item) use ($mainParentGlobalItem) {
//                /** @var GlobalItem $item */
//                $item->parent()->associate($mainParentGlobalItem->id);
//                $item->save();
//            });
//        $data = [$mainParentGlobalItem->toArray()];
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/globalItems', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'id' => $mainParentGlobalItem
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->first()->id,
//            'parent_id' => null
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItems->last()->id,
//            'parent_id' => null
//        ]);
//    }
//
//    public function testDeleteParentGlobalItemByIdWithSiteParentGlobalItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        $globalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//        $globalItem->parent()->associate($mainParentGlobalItem->id);
//        $globalItem->save();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/globalItem/' . $mainParentGlobalItem->id, [], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $globalItem->id,
//            'parent_id' => null
//        ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    public function testDeleteGlobalItemsWithSiteParentGlobalItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        /** @var GlobalItem[]|\Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)
//            ->create(['site_id' => $mainSite->id])
//            ->each(function ($item) use ($mainParentGlobalItem) {
//                /** @var GlobalItem $item */
//                $item->parent()->associate($mainParentGlobalItem->id);
//                $item->save();
//            });
//        $data = $globalItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/globalItems', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'id' => $globalItems->first()->id
//        ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'id' => $globalItems->last()->id
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $mainParentGlobalItem->id
//        ]);
//    }
//
//    public function testDeleteGlobalItemByIdWithSiteParentGlobalItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainParentGlobalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//
//        $globalItem = factory(GlobalItem::class)->create(['site_id' => $mainSite->id]);
//        $globalItem->parent()->associate($mainParentGlobalItem->id);
//        $globalItem->save();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/globalItem/' . $globalItem->id, [], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'id' => $mainParentGlobalItem->id
//        ]);
//
//        $this->assertDatabaseMissing('global_items', [
//            'id' => $globalItem->id
//        ]);
//    }
}
