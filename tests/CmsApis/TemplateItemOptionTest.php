<?php

namespace Tests\CmsApis;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\Language;
use App\Api\Models\Site;
use App\Api\Models\Template;
use App\Api\Models\TemplateItem;
use App\Api\Models\TemplateItemOption;
use Tests\CmsApiTestCase;

class TemplateItemOptionTest extends CmsApiTestCase
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
     * @var TemplateItem
     */
    private $templateItem;

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
        $this->templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
    }

    public function testGetTemplateItemOptions()
    {
        factory(TemplateItemOption::class, 3)
            ->create(['template_item_id' => $this->templateItem->id])
            ->each(function ($item) {
                /** @var TemplateItemOption $item */
                $item->string()->create([
                    'option_value' => 'TEST'
                ]);
            });
        $options = TemplateItemOption::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/templateItemOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $options
            ]);
    }

    public function testGetTemplateItemOptionById()
    {
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $option->string()->create([
            'option_value' => 'TEST'
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/templateItemOption/' . $option->id, self::$developerAuthorizationHeader);

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
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'template_item_id' => $this->templateItem->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreErrorWithInvalidElementType()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'template_item_id' => $this->templateItem->id,
            'element_type' => 'INTEGER'
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeString()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_type' => OptionValueConstants::STRING,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeInteger()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::INTEGER,
            'option_value' => self::$faker->randomNumber(),
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_type' => OptionValueConstants::INTEGER,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_integers', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeBoolean()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::INTEGER,
            'option_value' => "true",
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_type' => OptionValueConstants::INTEGER,
                'option_value' => 1,
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_integers', [
            'option_value' => 1
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }
    
    public function testStoreTypeDecimal()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DECIMAL,
            'option_value' => self::$faker->randomFloat(),
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_type' => OptionValueConstants::DECIMAL,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_decimals', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeDate()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DATE,
            'option_value' => self::$faker->dateTime->format('Y-m-d h:m:s'),
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_type' => OptionValueConstants::DATE,
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_dates', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreErrorWithDuplicateVariableNameWithinTemplateItem()
    {
        $templateItemOption = factory(TemplateItemOption::class)->create([
            'template_item_id' => $this->templateItem->id
        ]);
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => $templateItemOption->variable_name,
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testUpdateWithSameType()
    {
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $this->templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        /** @var TemplateItemOption|TemplateItemOption[]|\Illuminate\Support\Collection $optionWithValue */
        $optionWithValue = $options;
        $optionWithValue->transform(function ($item) {
            /** @var TemplateItemOption $item */
            $data = $item->withOptionValue();
            $data['option_type'] = OptionValueConstants::STRING;
            $data['option_value'] = 'NEW TEST';
            return $data;
        });
        $data = $optionWithValue->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

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

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => 'NEW TEST'
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateWithDifferentType()
    {
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $this->templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        /** @var TemplateItemOption|TemplateItemOption[]|\Illuminate\Support\Collection $optionWithValue */
        $optionWithValue = $options;
        $optionWithValue->transform(function ($item) {
            /** @var TemplateItemOption $item */
            $data = $item->withOptionValue();
            $data['option_type'] = OptionValueConstants::INTEGER;
            $data['option_value'] = 100;
            return $data;
        });
        $data = $optionWithValue->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

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

        $this->assertDatabaseHas('template_item_option_integers', [
            'option_value' => 100
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdWithSameType()
    {
        $option = factory(TemplateItemOption::class)->create([
            'template_item_id' => $this->templateItem->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['option_type'] = OptionValueConstants::STRING;
        $data['option_value'] = 'NEW TEST';

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => 'NEW TEST'
                ]
            ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => 'NEW TEST'
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdWithDifferentType()
    {
        $option = factory(TemplateItemOption::class)->create([
            'template_item_id' => $this->templateItem->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['option_type'] = OptionValueConstants::DATE;
        $data['option_value'] = self::$faker->dateTime->format('Y-m-d h:m:s');

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'option_type' => OptionValueConstants::DATE
                ]
            ]);

        $this->assertDatabaseHas('template_item_option_dates', [
            'option_value' => $data['option_value']
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testDelete()
    {
        /** @var TemplateItemOption|TemplateItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $this->templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/templateItemOptions', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => 'TEST'
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_id' => $options->first()->id
        ]);
    }

    public function testDeleteById()
    {
        $option = factory(TemplateItemOption::class)->create([
            'template_item_id' => $this->templateItem->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/templateItemOption/' . $option->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => 'TEST'
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_id' => $option->id
        ]);
    }

    public function testUpdateChangeTemplateItemId()
    {
        $newTemplateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);

        /** @var TemplateItemOption|TemplateItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $this->templateItem->id
        ])->each(function ($item) use ($newTemplateItem) {
            /** @var TemplateItemOption $item */
            $item->template_item_id = $newTemplateItem->id;
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });
        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'template_item_id' => $newTemplateItem->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $newTemplateItem->id
        ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdChangeTemplateItemId()
    {
        $newTemplateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create([
            'template_item_id' => $this->templateItem->id
        ]);
        $option->template_item_id = $newTemplateItem->id;
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id. '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'template_item_id' => $newTemplateItem->id
                ]
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $newTemplateItem->id
        ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateWithDifferentElementType()
    {
        $options = factory(TemplateItemOption::class, 3)
            ->create(['template_item_id' => $this->templateItem->id])
            ->each(function ($item) {
                /** @var TemplateItemOption $item */
                $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
                $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            });

        /** @var TemplateItemOption|TemplateItemOption[]|\Illuminate\Support\Collection $optionWithOptionElementType */
        $optionWithOptionElementType = $options;
        $optionWithOptionElementType->transform(function ($item) {
            /** @var TemplateItemOption $item */
            $data = $item->withOptionElementType();
            $data['element_type'] = OptionElementTypeConstants::CHECKBOX_LIST;
            $data['element_value'] = json_encode(['data' => 'data']);
            return $data;
        });
        $data = $optionWithOptionElementType->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

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
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['element_type'] = OptionElementTypeConstants::CHECKBOX_LIST;
        $data['element_value'] = json_encode(['data' => 'data']);

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

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

    //Nullable
    public function testStoreTypeStringNullable()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => null,
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_type' => OptionValueConstants::STRING,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeIntegerNullable()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::INTEGER,
            'option_value' => null,
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_type' => OptionValueConstants::INTEGER,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_integers', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeDecimalNullable()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DECIMAL,
            'option_value' => null,
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_type' => OptionValueConstants::DECIMAL,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_decimals', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeDateNullable()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DATE,
            'option_value' => null,
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_type' => OptionValueConstants::DATE,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_dates', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }
}
