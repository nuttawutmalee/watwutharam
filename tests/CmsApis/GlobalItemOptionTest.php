<?php

namespace Tests\CmsApis;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\Language;
use App\Api\Models\Site;
use Tests\CmsApiTestCase;

class GlobalItemOptionTest extends CmsApiTestCase
{
    /**
     * @var Site
     */
    private $site;

    /**
     * @var GlobalItem
     */
    private $globalItem;

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
        $this->globalItem = factory(GlobalItem::class)->create(['site_id' => $this->site->id]);
    }

    public function testGetGlobalItemOptions()
    {
        factory(GlobalItemOption::class, 3)
            ->create(['global_item_id' => $this->globalItem->id])
            ->each(function ($item) {
                /** @var GlobalItemOption $item */
                $item->string()->create([
                    'option_value' => 'TEST'
                ]);
            });
        $options = GlobalItemOption::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/globalItemOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $options
            ]);
    }

    public function testGetGlobalItemOptionById()
    {
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->string()->create([
            'option_value' => 'TEST'
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/globalItemOption/' . $option->id, self::$developerAuthorizationHeader);

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
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'global_item_id' => $this->globalItem->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreErrorWithInvalidElementType()
    {
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'global_item_id' => $this->globalItem->id,
            'element_type' => 'INTEGER'
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeString()
    {
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'global_item_id' => $this->globalItem->id,
                'option_type' => OptionValueConstants::STRING,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeBoolean()
    {
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::INTEGER,
            'option_value' => "true",
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'global_item_id' => $this->globalItem->id,
                'option_type' => OptionValueConstants::INTEGER,
                'option_value' => 1,
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('global_item_option_integers', [
            'option_value' => 1
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeInteger()
    {
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::INTEGER,
            'option_value' => self::$faker->randomNumber(),
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'global_item_id' => $this->globalItem->id,
                'option_type' => OptionValueConstants::INTEGER,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('global_item_option_integers', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeDecimal()
    {
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DECIMAL,
            'option_value' => self::$faker->randomFloat(),
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'global_item_id' => $this->globalItem->id,
                'option_type' => OptionValueConstants::DECIMAL,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('global_item_option_decimals', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeDate()
    {
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DATE,
            'option_value' => self::$faker->dateTime->format('Y-m-d h:m:s'),
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'global_item_id' => $this->globalItem->id,
                'option_type' => OptionValueConstants::DATE,
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('global_item_option_dates', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreErrorWithDuplicateVariableNameWithinGlobalItem()
    {
        $globalItemOption = factory(GlobalItemOption::class)->create([
            'global_item_id' => $this->globalItem->id
        ]);
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => $globalItemOption->variable_name,
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testUpdateWithSameType()
    {
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        /** @var GlobalItemOption|\Illuminate\Support\Collection $optionWithValue */
        $optionWithValue = $options;
        $optionWithValue->transform(function ($item) {
            /** @var GlobalItemOption $item */
            $data = $item->withOptionValue();
            $data['option_type'] = OptionValueConstants::STRING;
            $data['option_value'] = 'NEW TEST';
            return $data;
        });
        $data = $optionWithValue->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

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

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => 'NEW TEST'
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateWithDifferentType()
    {
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionWithValue */
        $optionWithValue = $options;
        $optionWithValue->transform(function ($item) {
            /** @var GlobalItemOption $item */
            $data = $item->withOptionValue();
            $data['option_type'] = OptionValueConstants::INTEGER;
            $data['option_value'] = 100;
            return $data;
        });
        $data = $optionWithValue->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

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

        $this->assertDatabaseHas('global_item_option_integers', [
            'option_value' => 100
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdWithSameType()
    {
        $option = factory(GlobalItemOption::class)->create([
            'global_item_id' => $this->globalItem->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['option_type'] = OptionValueConstants::STRING;
        $data['option_value'] = 'NEW TEST';

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => 'NEW TEST'
                ]
            ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => 'NEW TEST'
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdWithDifferentType()
    {
        $option = factory(GlobalItemOption::class)->create([
            'global_item_id' => $this->globalItem->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['option_type'] = OptionValueConstants::DATE;
        $data['option_value'] = self::$faker->dateTime->format('Y-m-d h:m:s');

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'option_type' => OptionValueConstants::DATE
                ]
            ]);

        $this->assertDatabaseHas('global_item_option_dates', [
            'option_value' => $data['option_value']
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testDelete()
    {
        /** @var GlobalItemOption|GlobalItemOption[] $options */
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/globalItemOptions', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => 'TEST'
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_id' => $options->first()->id
        ]);
    }

    public function testDeleteById()
    {
        $option = factory(GlobalItemOption::class)->create([
            'global_item_id' => $this->globalItem->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/globalItemOption/' . $option->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => 'TEST'
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_id' => $option->id
        ]);
    }

    public function testUpdateChangeGlobalItemId()
    {
        $newGlobalItem = factory(GlobalItem::class)->create(['site_id' => $this->site->id]);

        /** @var GlobalItemOption|GlobalItemOption[] $options */
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) use ($newGlobalItem) {
            /** @var GlobalItemOption $item */
            $item->global_item_id = $newGlobalItem->id;
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });
        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'global_item_id' => $newGlobalItem->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('global_item_options', [
            'global_item_id' => $newGlobalItem->id
        ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdChangeGlobalItemId()
    {
        $newGlobalItem = factory(GlobalItem::class)->create(['site_id' => $this->site->id]);
        $option = factory(GlobalItemOption::class)->create([
            'global_item_id' => $this->globalItem->id
        ]);
        $option->global_item_id = $newGlobalItem->id;
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id. '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'global_item_id' => $newGlobalItem->id
                ]
            ]);

        $this->assertDatabaseHas('global_item_options', [
            'global_item_id' => $newGlobalItem->id
        ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateWithDifferentElementType()
    {
        $options = factory(GlobalItemOption::class, 3)
            ->create(['global_item_id' => $this->globalItem->id])
            ->each(function ($item) {
                /** @var GlobalItemOption $item */
                $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
                $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionWithOptionElementType */
        $optionWithOptionElementType = $options;
        $optionWithOptionElementType->transform(function ($item) {
            /** @var GlobalItemOption $item */
            $data = $item->withOptionElementType();
            $data['element_type'] = OptionElementTypeConstants::CHECKBOX_LIST;
            $data['element_value'] = json_encode(['data' => 'data']);
            return $data;
        });
        $data = $optionWithOptionElementType->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

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
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['element_type'] = OptionElementTypeConstants::CHECKBOX_LIST;
        $data['element_value'] = json_encode(['data' => 'data']);

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

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
