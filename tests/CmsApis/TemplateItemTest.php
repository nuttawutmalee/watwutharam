<?php

namespace Tests\CmsApis;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\Component;
use App\Api\Models\ComponentOption;
use App\Api\Models\Language;
use App\Api\Models\Site;
use App\Api\Models\Template;
use App\Api\Models\TemplateItem;
use App\Api\Models\TemplateItemOption;
use Tests\CmsApiTestCase;

class TemplateItemTest extends CmsApiTestCase
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
     * @var Template
     */
    private $template;

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

        $this->template = factory(Template::class)->create(['site_id' => $site->id]);
    }

    public function testGetAllTemplateItems()
    {
        factory(TemplateItem::class, 3)->create();
        $items = TemplateItem::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/templateItems', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $items
            ]);
    }

    public function testGetTemplateItemById()
    {
        $item = factory(TemplateItem::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/templateItem/' . $item->id, self::$developerAuthorizationHeader);

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
        $template = factory(Template::class)->create();
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'variable_name' => self::randomVariableName(),
            'template_id' => $template->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'template_id' => $template->id,
                    'name' => 'MOCK-UP TEMPLATE ITEM',
                    'display_order' => 1
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'template_id' => $template->id,
            'name' => 'MOCK-UP TEMPLATE ITEM',
        ]);
    }

    public function testStoreWithComponent()
    {
        $component = factory(Component::class)->create();
        $template = factory(Template::class)->create();
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'variable_name' => self::randomVariableName(),
            'template_id' => $template->id,
            'component_id' => $component->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'template_id' => $template->id,
                    'component_id' => $component->id,
                    'name' => 'MOCK-UP TEMPLATE ITEM',
                    'display_order' => 1
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'template_id' => $template->id,
            'component_id' => $component->id,
            'name' => 'MOCK-UP TEMPLATE ITEM'
        ]);
    }

    public function testStoreDuplicateVariableNameError()
    {
        $template = factory(Template::class)->create();
        $item = factory(TemplateItem::class)->create([
            'template_id' => $template->id
        ]);
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'variable_name' => $item->variable_name,
            'template_id' => $template->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('template_items', [
            'name' => 'MOCK-UP TEMPLATE ITEM'
        ]);
    }

    public function testStoreErrorWithoutVariableNameAndComponentId()
    {
        $template = factory(Template::class)->create();
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'template_id' => $template->id,
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('template_items', [
            'name' => 'MOCK-UP TEMPLATE ITEM'
        ]);
    }

    public function testUpdateNewComponent()
    {
        $component = factory(Component::class)->create();
        $items = factory(TemplateItem::class, 3)->create();
        $data = $items->each(function ($item) use ($component) {
            $item->component_id = $component->id;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItems/update', ['data' => $data], $header);

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

        $this->assertDatabaseHas('template_items', [
            'component_id' => null
        ]);
    }

    public function testUpdateNewTemplate()
    {
        $template = factory(Template::class)->create();
        $items = factory(TemplateItem::class, 3)->create();
        $data = $items->each(function ($item) use ($template) {
            $item->template_id = $template->id;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItems/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'template_id' => $template->id,
                        'template' => [
                            'id' => $template->id
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'template_id' => $template->id
        ]);
    }

    public function testUpdateNewVariableName()
    {
        $items = factory(TemplateItem::class, 3)->create([
            'variable_name' => 'new_variable_name'
        ]);
        $data = $items->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItems/update', ['data' => $data], $header);

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

        $this->assertDatabaseHas('template_items', [
            'variable_name' => 'new_variable_name'
        ]);
    }

    public function testUpdateNewDuplicateVariableNameErrorInTheSameParentTemplate()
    {
        $template = factory(Template::class)->create();
        $items = factory(TemplateItem::class, 3)->create([
            'template_id' => $template->id,
        ]);
        $data = $items->each(function ($item) use ($template) {
            $item->variable_name = 'new_variable_name';
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItems/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('template_items', [
            'variable_name' => 'new_variable_name'
        ]);
    }

    public function testUpdateByIdNewComponent()
    {
        $component = factory(Component::class)->create();
        $item = factory(TemplateItem::class)->create();
        $item->component_id = $component->id;
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'component_id' => null
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'component_id' => null
        ]);
    }

    public function testUpdateByIdNewTemplate()
    {
        $template = factory(Template::class)->create();
        $item = factory(TemplateItem::class)->create();
        $item->template_id = $template->id;
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'template_id' => $template->id,
                    'template' => [
                        'id' => $template->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'template_id' => $template->id
        ]);
    }

    public function testUpdateByIdNewVariableName()
    {
        $item = factory(TemplateItem::class)->create([
            'variable_name' => 'new_variable_name'
        ]);
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem/' . $item->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'variable_name' => 'new_variable_name'
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'variable_name' => 'new_variable_name'
        ]);
    }

    public function testUpdateByIdNewDuplicateVariableNameErrorInTheSameParentTemplate()
    {
        $template = factory(Template::class)->create();
        factory(TemplateItem::class)->create([
            'variable_name' => 'new_variable_name',
            'template_id' => $template->id
        ]);
        $item = factory(TemplateItem::class)->create([
            'template_id' => $template->id,
        ]);
        $item->variable_name = 'new_variable_name';
        $data = $item->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem/' . $item->id . '/update', ['data' => $data], $header);
        $response
            ->assertJson([
                'result' => false
            ]);
    }

    public function testDelete()
    {
        $items = factory(TemplateItem::class, 3)->create();
        $data = $items->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/templateItems', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('template_items', [
            'id' => $items->first()->id
        ]);
    }

    public function testDeleteById()
    {
        $item = factory(TemplateItem::class)->create();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/templateItem/' . $item->id, [], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('template_items', [
            'id' => $item->id
        ]);
    }

    //Display order
    //Same parent template
    public function testGetDisplayOrderSameTemplates()
    {
        $template = factory(Template::class)->create();
        $items = factory(TemplateItem::class, 3)->create([
            'template_id' => $template->id
        ])->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/templateItems', self::$developerAuthorizationHeader);

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
    public function testCreateDisplayOrderSameTemplateWhereOrderIsMoreThanMaxOrder()
    {
        $template = factory(Template::class)->create();
        $items = factory(TemplateItem::class, 3)->create(['template_id' => $template->id]);
        $maxOrder = $items->last()->display_order;

        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'variable_name' => self::randomVariableName(),
            'template_id' => $template->id,
            'display_order' => $maxOrder + 10
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP TEMPLATE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'display_order' => $maxOrder + 1
        ]);
    }

    //order == max
    public function testCreateDisplayOrderSameTemplateWhereOrderIsEqualMaxOrder()
    {
        $template = factory(Template::class)->create();
        $items = factory(TemplateItem::class, 3)->create(['template_id' => $template->id]);
        $maxOrder = $items->last()->display_order;

        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'variable_name' => self::randomVariableName(),
            'template_id' => $template->id,
            'display_order' => $maxOrder
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP TEMPLATE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'display_order' => $maxOrder + 1
        ]);

    }

    //order < max
    public function testCreateDisplayOrderSameTemplateWhereOrderIsLessThanMaxOrder()
    {
        $template = factory(Template::class)->create();
        $items = factory(TemplateItem::class, 3)->create(['template_id' => $template->id]);
        $maxOrder = $items->last()->display_order;

        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'variable_name' => self::randomVariableName(),
            'template_id' => $template->id,
            'display_order' => $maxOrder - 1
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP TEMPLATE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'display_order' => $maxOrder + 1
        ]);
    }

    //order < min
    public function testCreateDisplayOrderSameTemplateWhereOrderIsLessThanMinOrder()
    {
        $template = factory(Template::class)->create();
        $items = factory(TemplateItem::class, 3)->create(['template_id' => $template->id]);
        $maxOrder = $items->last()->display_order;
        $minOrder = $items->first()->display_order;

        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'variable_name' => self::randomVariableName(),
            'template_id' => $template->id,
            'display_order' => $minOrder - 1
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP TEMPLATE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'display_order' => $maxOrder + 1
        ]);
    }

    //order == null
    public function testCreateDisplayOrderSameTemplateWhereOrderIsNull()
    {
        $template = factory(Template::class)->create();
        $items = factory(TemplateItem::class, 3)->create(['template_id' => $template->id]);
        $maxOrder = $items->last()->display_order;

        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'variable_name' => self::randomVariableName(),
            'template_id' => $template->id,
            'display_order' => null
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP TEMPLATE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'display_order' => $maxOrder + 1
        ]);
    }

    //order is not present
    public function testCreateDisplayOrderSameTemplateWhereOrderIsNotPresent()
    {
        $template = factory(Template::class)->create();
        $items = factory(TemplateItem::class, 3)->create(['template_id' => $template->id]);
        $maxOrder = $items->last()->display_order;

        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'variable_name' => self::randomVariableName(),
            'template_id' => $template->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'name' => 'MOCK-UP TEMPLATE ITEM',
                    'display_order' => $maxOrder + 1
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'display_order' => $maxOrder + 1
        ]);
    }

    //Different parent template
    public function testGetDisplayOrderDifferentTemplates()
    {
        $items = factory(TemplateItem::class, 3)->create()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/templateItems', self::$developerAuthorizationHeader);

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
    public function testGetAllTemplateOptionsByTemplateItemId()
    {
        $templateItem = factory(TemplateItem::class)->create();

        /** @var TemplateItemOption|TemplateItemOption[]|\Illuminate\Support\Collection $templateItemOptions */
        $templateItemOptions = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->string()->create([
                'option_value' => 'TEST'
            ]);
        });

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/templateItem/' . $templateItem->id . '/templateItemOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                       'id' => $templateItemOptions->first()->id,
                        'option_type' => OptionValueConstants::STRING,
                        'option_value' => 'TEST'
                    ]
                ]
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
            'name' => 'MOCK-UP TEMPLATE ITEM',
            'variable_name' => self::randomVariableName(),
            'template_id' => $this->template->id,
            'component_id' => $component->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItem', $params, $header);

        /** @var \Dingo\Api\Http\Response|\Illuminate\Foundation\Testing\TestResponse $response */
        $content = json_decode($response->getContent());
        $data = $content->data;

        /** @var TemplateItem $templateItem */
        $templateItem = TemplateItem::findOrFail($data->id);
        $options = $templateItem->templateItemOptions;

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'template_id' => $this->template->id,
                    'component_id' => $component->id,
                    'name' => 'MOCK-UP TEMPLATE ITEM'
                ]
            ]);

        $this->assertDatabaseHas('template_items', [
            'template_id' => $this->template->id,
            'component_id' => $component->id,
            'name' => 'MOCK-UP TEMPLATE ITEM'
        ]);

        //Option Value
        $this->assertDatabaseHas('template_item_option_strings', [
            'template_item_option_id' => $options->first()->id,
            'option_value' => 'TEST'
        ]);

        //Element Type
        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);

        //Site Translation
        $this->assertDatabaseHas('site_translations', [
            'item_id' => $options->first()->id,
            'item_type' => class_basename(new TemplateItemOption),
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'item_id' => $options->first()->id,
            'item_type' => class_basename(new TemplateItemOption),
            'translated_text' => 'เทส'
        ]);
    }

    //Parent
    //Create with non-site parent template item
//    public function testCreateTemplateItemWithNonSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//
//        $differentSite = factory(Site::class)->create();
//        $differentTemplate = factory(Template::class)->create(['site_id' => $differentSite->id]);
//        $differentParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $differentTemplate->id]);
//
//        $params = [
//            'name' => 'MOCK-UP TEMPLATE ITEM',
//            'variable_name' => self::randomVariableName(),
//            'friendly_url' => self::$faker->slug,
//            'description' => self::$faker->sentence(),
//            'template_id' => $mainTemplate->id,
//            'parent_id' => $differentParentTemplateItem->id
//        ];
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItem', $params, $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'name' => $params['name'],
//            'friendly_url' => $params['friendly_url'],
//            'template_id' => $mainTemplate->id,
//            'parent_id' => $differentParentTemplateItem->id
//        ]);
//    }
//
//    //Create with site parent template item
//    public function testCreateTemplateItemWithSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $params = [
//            'name' => 'MOCK-UP TEMPLATE ITEM',
//            'variable_name' => self::randomVariableName(),
//            'friendly_url' => self::$faker->slug,
//            'description' => self::$faker->sentence(),
//            'template_id' => $mainTemplate->id,
//            'parent_id' => $mainParentTemplateItem->id
//        ];
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItem', $params, $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'parent' => [
//                        'id' => $mainParentTemplateItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'name' => $params['name'],
//            'template_id' => $mainTemplate->id,
//            'parent_id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    //Update no -> non-site parent template item
//    public function testUpdateTemplateItemsWithNoParentTemplateItemToNonSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//
//        $differentSite = factory(Site::class)->create();
//        $differentTemplate = factory(Template::class)->create(['site_id' => $differentSite->id]);
//        $differentParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $differentTemplate->id]);
//
//        $templateItems = factory(TemplateItem::class, 3)
//            ->create(['template_id' => $mainTemplate->id])
//            ->each(function ($item) use ($differentParentTemplateItem) {
//                $item['parent_id'] = $differentParentTemplateItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $templateItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'name' => 'UPDATED',
//            'parent_id' => $differentParentTemplateItem->id
//        ]);
//    }
//
//    public function testUpdateTemplateItemByIdWithNoParentTemplateItemToNonSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//
//        $differentSite = factory(Site::class)->create();
//        $differentTemplate = factory(Template::class)->create(['site_id' => $differentSite->id]);
//        $differentParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $differentTemplate->id]);
//
//        $templateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//        $templateItem['parent_id'] = $differentParentTemplateItem->id;
//        $templateItem->name = 'UPDATED';
//        $data = $templateItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItem/' . $templateItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'name' => 'UPDATED',
//            'parent_id' => $differentParentTemplateItem->id
//        ]);
//    }
//
//    //Update no -> site parent template item
//    public function testUpdateTemplateItemsWithNoParentTemplateItemToSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItems = factory(TemplateItem::class, 3)
//            ->create(['template_id' => $mainTemplate->id])
//            ->each(function ($item) use ($mainParentTemplateItem) {
//                $item['parent_id'] = $mainParentTemplateItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $templateItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItems/update', ['data' => $data], $header);
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
//                'id' => $mainParentTemplateItem->id
//            ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentTemplateItem->id
//        ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItems->last()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    public function testUpdateTemplateItemByIdWithNoParentTemplateItemToSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//        $templateItem['parent_id'] = $mainParentTemplateItem->id;
//        $templateItem->name = 'UPDATED';
//        $data = $templateItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItem/' . $templateItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'name' => 'UPDATED',
//                    'parent' => [
//                        'id' => $mainParentTemplateItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    //Update no -> no
//    public function testUpdateTemplateItemsWithNoParentTemplateItemToNoParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//
//        $templateItems = factory(TemplateItem::class, 3)
//            ->create(['template_id' => $mainTemplate->id])
//            ->each(function ($item) {
//                $item['parent_id'] = null;
//                $item->name = 'UPDATED';
//            });
//        $data = $templateItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItems/update', ['data' => $data], $header);
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
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => null
//        ]);
//    }
//
//    public function testUpdateTemplateItemByIdWithNoParentTemplateItemToNoParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//
//        $templateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//        $templateItem['parent_id'] = null;
//        $templateItem->name = 'UPDATED';
//        $data = $templateItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItem/' . $templateItem->id . '/update', ['data' => $data], $header);
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
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => null
//        ]);
//    }
//
//    //Update site parent template item -> non site parent template item
//    public function testUpdateTemplateItemsWithSiteParentTemplateItemToNonSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $differentTemplateItem = factory(TemplateItem::class)->create();
//
//        $templateItems = factory(TemplateItem::class, 3)
//            ->create(['template_id' => $mainTemplate->id])
//            ->each(function ($item) use ($mainParentTemplateItem, $differentTemplateItem) {
//                $item->parent()->associate($mainParentTemplateItem->id);
//                $item->save();
//
//                $item['parent_id'] = $differentTemplateItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $templateItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItems/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'id' => $templateItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $differentTemplateItem->id
//        ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItems->first()->id,
//            'parent_id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    public function testUpdateTemplateItemByIdWithSiteParentTemplateItemToNonSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $differentTemplateItem = factory(TemplateItem::class)->create();
//
//        $templateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//        $templateItem->parent()->associate($mainParentTemplateItem->id);
//        $templateItem->save();
//
//        $templateItem['parent_id'] = $differentTemplateItem->id;
//        $templateItem->name = 'UPDATED';
//        $data = $templateItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItem/' . $templateItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'id' => $templateItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => $differentTemplateItem->id
//        ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItem->id,
//            'parent_id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    //Update site parent template item -> new parent template item
//    public function testUpdateTemplateItemsWithSiteParentTemplateItemToNewSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $newParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItems = factory(TemplateItem::class, 3)
//            ->create(['template_id' => $mainTemplate->id])
//            ->each(function ($item) use ($mainParentTemplateItem, $newParentTemplateItem) {
//                $item->parent()->associate($mainParentTemplateItem->id);
//                $item->save();
//
//                $item['parent_id'] = $newParentTemplateItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $templateItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItems/update', ['data' => $data], $header);
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
//                'id' => $newParentTemplateItem->id
//            ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $newParentTemplateItem->id
//        ]);
//    }
//
//    public function testUpdateTemplateItemByIdWithSiteParentTemplateItemToNewSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $newParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//        $templateItem->parent()->associate($mainParentTemplateItem->id);
//        $templateItem->save();
//
//        $templateItem['parent_id'] = $newParentTemplateItem->id;
//        $templateItem->name = 'UPDATED';
//        $data = $templateItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItem/' . $templateItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'name' => 'UPDATED',
//                    'parent' => [
//                        'id' => $newParentTemplateItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => $newParentTemplateItem->id
//        ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'id' => $templateItem->id,
//            'parent_id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    //Update site parent template item -> same site parent template item
//    public function testUpdateTemplateItemsWithSiteParentTemplateItemToSameSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItems = factory(TemplateItem::class, 3)
//            ->create(['template_id' => $mainTemplate->id])
//            ->each(function ($item) use ($mainParentTemplateItem) {
//                $item->parent()->associate($mainParentTemplateItem->id);
//                $item->save();
//
//                $item['parent_id'] = $mainParentTemplateItem->id;
//                $item->name = 'UPDATED';
//            });
//        $data = $templateItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItems/update', ['data' => $data], $header);
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
//                'id' => $mainParentTemplateItem->id
//            ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItems->first()->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    public function testUpdateTemplateItemByIdWithSiteParentTemplateItemToSameSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//        $templateItem->parent()->associate($mainParentTemplateItem->id);
//        $templateItem->save();
//
//        $templateItem['parent_id'] = $mainParentTemplateItem->id;
//        $templateItem->name = 'UPDATED';
//        $data = $templateItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItem/' . $templateItem->id . '/update', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => [
//                    'name' => 'UPDATED',
//                    'parent' => [
//                        'id' => $mainParentTemplateItem->id
//                    ]
//                ]
//            ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItem->id,
//            'name' => 'UPDATED',
//            'parent_id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    //Update site parent template item -> no
//    public function testUpdateTemplateItemsWithSiteParentTemplateItemToNoSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItems = factory(TemplateItem::class, 3)
//            ->create(['template_id' => $mainTemplate->id])
//            ->each(function ($item) use ($mainParentTemplateItem) {
//                $item->parent()->associate($mainParentTemplateItem->id);
//                $item->save();
//
//                $item['parent_id'] = null;
//                $item->name = 'UPDATED';
//            });
//        $data = $templateItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItems/update', ['data' => $data], $header);
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
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItems->first()->id,
//            'name' => 'UPDATED'
//        ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'id' => $templateItems->first()->id,
//            'parent_id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    public function testUpdateTemplateItemByIdWithSiteParentTemplateItemToNoSiteParentTemplateItem()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//        $templateItem->parent()->associate($mainParentTemplateItem->id);
//        $templateItem->save();
//
//        $templateItem['parent_id'] = null;
//        $templateItem->name = 'UPDATED';
//        $data = $templateItem->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/templateItem/' . $templateItem->id . '/update', ['data' => $data], $header);
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
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItem->id,
//            'name' => 'UPDATED'
//        ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'id' => $templateItem->id,
//            'parent_id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    //Delete
//    public function testDeleteParentTemplateItemsWithSiteParentTemplateItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItems = factory(TemplateItem::class, 3)
//            ->create(['template_id' => $mainTemplate->id])
//            ->each(function ($item) use ($mainParentTemplateItem) {
//                $item->parent()->associate($mainParentTemplateItem->id);
//                $item->save();
//            });
//        $data = [$mainParentTemplateItem->toArray()];
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/templateItems', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'id' => $mainParentTemplateItem
//        ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItems->first()->id,
//            'parent_id' => null
//        ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItems->last()->id,
//            'parent_id' => null
//        ]);
//    }
//
//    public function testDeleteParentTemplateItemByIdWithSiteParentTemplateItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//        $templateItem->parent()->associate($mainParentTemplateItem->id);
//        $templateItem->save();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/templateItem/' . $mainParentTemplateItem->id, [], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $templateItem->id,
//            'parent_id' => null
//        ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    public function testDeleteTemplateItemsWithSiteParentTemplateItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItems = factory(TemplateItem::class, 3)
//            ->create(['template_id' => $mainTemplate->id])
//            ->each(function ($item) use ($mainParentTemplateItem) {
//                $item->parent()->associate($mainParentTemplateItem->id);
//                $item->save();
//            });
//        $data = $templateItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/templateItems', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'id' => $templateItems->first()->id
//        ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'id' => $templateItems->last()->id
//        ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $mainParentTemplateItem->id
//        ]);
//    }
//
//    public function testDeleteTemplateItemByIdWithSiteParentTemplateItemsCascade()
//    {
//        $mainSite = factory(Site::class)->create();
//        $mainTemplate = factory(Template::class)->create(['site_id' => $mainSite->id]);
//        $mainParentTemplateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//
//        $templateItem = factory(TemplateItem::class)->create(['template_id' => $mainTemplate->id]);
//        $templateItem->parent()->associate($mainParentTemplateItem->id);
//        $templateItem->save();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->delete(self::$apiPrefix . '/templateItem/' . $templateItem->id, [], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true,
//                'data' => null
//            ]);
//
//        $this->assertDatabaseHas('template_items', [
//            'id' => $mainParentTemplateItem->id
//        ]);
//
//        $this->assertDatabaseMissing('template_items', [
//            'id' => $templateItem->id
//        ]);
//    }
}

