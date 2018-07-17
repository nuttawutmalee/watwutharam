<?php

namespace Tests\CmsApis;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\Language;
use App\Api\Models\Page;
use App\Api\Models\PageItem;
use App\Api\Models\PageItemOption;
use App\Api\Models\Site;
use App\Api\Models\Template;
use Tests\CmsApiTestCase;

class PageItemOptionTest extends CmsApiTestCase
{
    /**
     * @var Site
     */
    private $site;

    /**
     * @var Template
     */
    private $template;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var PageItem
     */
    private $pageItem;

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();

        $this->site = factory(Site::class)->create();

        $english = Language::firstOrCreate([
            'code' => 'en',
            'name' => 'English'
        ]);

        $this->site->languages()->save($english, ['is_main' => true]);

        $this->template = factory(Template::class)->create(['site_id' => $this->site->id]);
        $this->page = factory(Page::class)->create(['template_id' => $this->template->id]);
        $this->pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
    }

    public function testGetPageItemOptions()
    {
        factory(PageItemOption::class, 3)
            ->create(['page_item_id' => $this->pageItem->id])
            ->each(function ($item) {
                /** @var PageItemOption $item */
                $item->string()->create([
                    'option_value' => 'TEST'
                ]);
        });
        $options = PageItemOption::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/pageItemOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $options
            ]);
    }

    public function testGetPageItemOptionById()
    {
        $option = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $option->string()->create([
            'option_value' => 'TEST'
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/pageItemOption/' . $option->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'id' => $option->id,
                    'option_value' => 'TEST'
                ]
            ]);
    }

    public function testStoreErrorWithoutElementType()
    {
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'page_item_id' => $this->pageItem->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreErrorWithInvalidElementType()
    {
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'page_item_id' => $this->pageItem->id,
            'element_type' => 'INTEGER'
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeString()
    {
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'page_item_id' => $this->pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'page_item_id' => $this->pageItem->id,
                'option_type' => OptionValueConstants::STRING,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeInteger()
    {
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::INTEGER,
            'option_value' => self::$faker->randomNumber(),
            'page_item_id' => $this->pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'page_item_id' => $this->pageItem->id,
                'option_type' => OptionValueConstants::INTEGER,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('page_item_option_integers', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeBoolean()
    {
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::INTEGER,
            'option_value' => "true",
            'page_item_id' => $this->pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'page_item_id' => $this->pageItem->id,
                'option_type' => OptionValueConstants::INTEGER,
                'option_value' => 1,
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('page_item_option_integers', [
            'option_value' => 1
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new PageItemOption()),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeDecimal()
    {
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DECIMAL,
            'option_value' => self::$faker->randomFloat(),
            'page_item_id' => $this->pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'page_item_id' => $this->pageItem->id,
                'option_type' => OptionValueConstants::DECIMAL,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('page_item_option_decimals', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeDate()
    {
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DATE,
            'option_value' => self::$faker->dateTime->format('Y-m-d h:m:s'),
            'page_item_id' => $this->pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'page_item_id' => $this->pageItem->id,
                'option_type' => OptionValueConstants::DATE,
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('page_item_option_dates', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreErrorWithDuplicateVariableNameWithinPageItem()
    {
        $pageItemOption = factory(PageItemOption::class)->create([
            'page_item_id' => $this->pageItem->id
        ]);
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => $pageItemOption->variable_name,
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'page_item_id' => $this->pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testUpdateWithSameType()
    {
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $this->pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionWithValue */
        $optionWithValue = $options;
        $optionWithValue->transform(function ($item) {
            /** @var PageItemOption $item */
            $data = $item->withOptionValue();
            $data['option_type'] = OptionValueConstants::STRING;
            $data['option_value'] = 'NEW TEST';
            return $data;
        });
        $data = $optionWithValue->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'option_type' => OptionValueConstants::STRING,
                        'option_value' => 'NEW TEST'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => 'NEW TEST'
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateWithDifferentType()
    {
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $this->pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionWithValue */
        $optionWithValue = $options;
        $optionWithValue->transform(function ($item) {
            /** @var PageItemOption $item */
            $data = $item->withOptionValue();
            $data['option_type'] = OptionValueConstants::INTEGER;
            $data['option_value'] = 100;
            return $data;
        });
        $data = $optionWithValue->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'option_type' => OptionValueConstants::INTEGER,
                        'option_value' => 100
                    ]
                ]
            ]);

        $this->assertDatabaseHas('page_item_option_integers', [
            'option_value' => 100
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdWithSameType()
    {
        $option = factory(PageItemOption::class)->create([
            'page_item_id' => $this->pageItem->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['option_type'] = OptionValueConstants::STRING;
        $data['option_value'] = 'NEW TEST';

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => 'NEW TEST'
                ]
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => 'NEW TEST'
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdWithDifferentType()
    {
        $option = factory(PageItemOption::class)->create([
            'page_item_id' => $this->pageItem->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['option_type'] = OptionValueConstants::DATE;
        $data['option_value'] = self::$faker->dateTime->format('Y-m-d h:m:s');

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'option_type' => OptionValueConstants::DATE
                ]
            ]);

        $this->assertDatabaseHas('page_item_option_dates', [
            'option_value' => $data['option_value']
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testDelete()
    {
        /** @var PageItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $this->pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/pageItemOptions', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => 'TEST'
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_id' => $options->first()->id
        ]);
    }

    public function testDeleteById()
    {
        $option = factory(PageItemOption::class)->create([
            'page_item_id' => $this->pageItem->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/pageItemOption/' . $option->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => 'TEST'
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_id' => $option->id
        ]);
    }

    public function testUpdateChangePageItemId()
    {
        $newPageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);

        /** @var PageItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $this->pageItem->id
        ])->each(function ($item) use ($newPageItem) {
            /** @var PageItemOption $item */
            $item->page_item_id = $newPageItem->id;
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });
        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'page_item_id' => $newPageItem->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('page_item_options', [
            'page_item_id' => $newPageItem->id
        ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdChangePageItemId()
    {
        $newPageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create([
            'page_item_id' => $this->pageItem->id
        ]);
        $option->page_item_id = $newPageItem->id;
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id. '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'page_item_id' => $newPageItem->id
                ]
            ]);

        $this->assertDatabaseHas('page_item_options', [
            'page_item_id' => $newPageItem->id
        ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateWithDifferentElementType()
    {
        $options = factory(PageItemOption::class, 3)
            ->create(['page_item_id' => $this->pageItem->id])
            ->each(function ($item) {
                /** @var PageItemOption $item */
                $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
                $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionWithOptionElementType */
        $optionWithOptionElementType = $options;
        $optionWithOptionElementType->transform(function ($item) {
            /** @var PageItemOption $item */
            $data = $item->withOptionElementType();
            $data['element_type'] = OptionElementTypeConstants::CHECKBOX_LIST;
            $data['element_value'] = json_encode(['data' => 'data']);
            return $data;
        });
        $data = $optionWithOptionElementType->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'element_type' => OptionElementTypeConstants::CHECKBOX_LIST,
                        'element_value' => json_encode(['data' => 'data'])
                    ]
                ]
            ]);

        $this->assertDatabaseHas('option_element_types', [
            'element_type' => OptionElementTypeConstants::CHECKBOX_LIST,
            'element_value' => json_encode(['data' => 'data'])
        ]);
    }

    public function testUpdateByIdWithDifferentElementType()
    {
        $option = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['element_type'] = OptionElementTypeConstants::CHECKBOX_LIST;
        $data['element_value'] = json_encode(['data' => 'data']);

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'element_type' => OptionElementTypeConstants::CHECKBOX_LIST,
                    'element_value' => json_encode(['data' => 'data'])
                ]
            ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_id' => $option->id,
            'element_type' => OptionElementTypeConstants::CHECKBOX_LIST,
            'element_value' => json_encode(['data' => 'data'])
        ]);
    }
}
