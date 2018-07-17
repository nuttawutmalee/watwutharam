<?php

namespace Tests\CmsApis;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\Component;
use App\Api\Models\ComponentOption;
use Tests\CmsApiTestCase;

class ComponentOptionTest extends CmsApiTestCase
{
    public function testGetComponentOptions()
    {
        factory(ComponentOption::class, 3)->create()->each(function ($item) {
            /** @var ComponentOption $item */
            $item->string()->create([
                'option_value' => 'TEST'
            ]);
        });
        $options = ComponentOption::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/componentOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $options
            ]);
    }

    public function testGetComponentOptionById()
    {
        $option = factory(ComponentOption::class)->create();
        $option->string()->create([
            'option_value' => 'TEST'
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/componentOption/' . $option->id, self::$developerAuthorizationHeader);

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
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'component_id' => $component->id
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreErrorWithInvalidElementType()
    {
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'component_id' => $component->id,
            'element_type' => 'INTEGER'
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeString()
    {
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'component_id' => $component->id,
                'option_type' => OptionValueConstants::STRING,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeInteger()
    {
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::INTEGER,
            'option_value' => self::$faker->randomNumber(),
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'component_id' => $component->id,
                'option_type' => OptionValueConstants::INTEGER,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_integers', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeBoolean()
    {
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::INTEGER,
            'option_value' => "true",
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'component_id' => $component->id,
                'option_type' => OptionValueConstants::INTEGER,
                'option_value' => 1,
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_integers', [
            'option_value' => 1
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeDecimal()
    {
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DECIMAL,
            'option_value' => self::$faker->randomFloat(),
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'component_id' => $component->id,
                'option_type' => OptionValueConstants::DECIMAL,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_decimals', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeDate()
    {
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DATE,
            'option_value' => self::$faker->dateTime->format('Y-m-d h:m:s'),
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'component_id' => $component->id,
                'option_type' => OptionValueConstants::DATE,
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_dates', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreErrorWithDuplicateVariableNameWithinComponent()
    {
        $component = factory(Component::class)->create();
        $componentOption = factory(ComponentOption::class)->create([
            'component_id' => $component->id
        ]);
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => $componentOption->variable_name,
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testUpdateWithSameType()
    {
        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionWithValue */
        $optionWithValue = $options;
        $optionWithValue->transform(function ($item) {
            /** @var ComponentOption $item */
            $data = $item->withOptionValue();
            $data['option_type'] = OptionValueConstants::STRING;
            $data['option_value'] = 'NEW TEST';
            return $data;
        });
        $data = $optionWithValue->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

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

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => 'NEW TEST'
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateWithDifferentType()
    {
        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionWithValue */
        $optionWithValue = $options;
        $optionWithValue->transform(function ($item) {
            /** @var ComponentOption $item */
            $data = $item->withOptionValue();
            $data['option_type'] = OptionValueConstants::INTEGER;
            $data['option_value'] = 100;
            return $data;
        });
        $data = $optionWithValue->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

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

        $this->assertDatabaseHas('component_option_integers', [
            'option_value' => 100
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdWithSameType()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create([
            'component_id' => $component->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['option_type'] = OptionValueConstants::STRING;
        $data['option_value'] = 'NEW TEST';

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => 'NEW TEST'
                ]
            ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => 'NEW TEST'
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdWithDifferentType()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create([
            'component_id' => $component->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['option_type'] = OptionValueConstants::DATE;
        $data['option_value'] = self::$faker->dateTime->format('Y-m-d h:m:s');

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'option_type' => OptionValueConstants::DATE
                ]
            ]);

        $this->assertDatabaseHas('component_option_dates', [
            'option_value' => $data['option_value']
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testDelete()
    {
        $component = factory(Component::class)->create();

        /** @var ComponentOption|ComponentOption[] $options */
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });

        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/componentOptions', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => 'TEST'
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_id' => $options->first()->id
        ]);
    }

    public function testDeleteById()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create([
            'component_id' => $component->id
        ]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/componentOption/' . $option->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => 'TEST'
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_id' => $option->id
        ]);
    }

    public function testUpdateChangeComponentId()
    {
        $component = factory(Component::class)->create();
        $newComponent = factory(Component::class)->create();

        /** @var ComponentOption|ComponentOption[] $options */
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) use ($newComponent) {
            /** @var ComponentOption $item */
            $item->component_id = $newComponent->id;
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        });
        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'component_id' => $newComponent->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $newComponent->id
        ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateByIdChangeComponentId()
    {
        $component = factory(Component::class)->create();
        $newComponent = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create([
            'component_id' => $component->id
        ]);
        $option->component_id = $newComponent->id;
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id. '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'component_id' => $newComponent->id
                ]
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $newComponent->id
        ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => 'TEST'
        ]);
    }

    public function testUpdateWithDifferentElementType()
    {
        $options = factory(ComponentOption::class, 3)
            ->create()
            ->each(function ($item) {
                /** @var ComponentOption $item */
                $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
                $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionWithOptionElementType */
        $optionWithOptionElementType = $options;
        $optionWithOptionElementType->transform(function ($item) {
            /** @var ComponentOption $item */
            $data = $item->withOptionElementType();
            $data['element_type'] = OptionElementTypeConstants::CHECKBOX_LIST;
            $data['element_value'] = json_encode(['data' => 'data']);
            return $data;
        });
        $data = $optionWithOptionElementType->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

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
        $option = factory(ComponentOption::class)->create();
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);

        $data = $option->toArray();
        $data['element_type'] = OptionElementTypeConstants::CHECKBOX_LIST;
        $data['element_value'] = json_encode(['data' => 'data']);

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

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
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => null,
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'component_id' => $component->id,
                'option_type' => OptionValueConstants::STRING,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeIntegerNullable()
    {
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::INTEGER,
            'option_value' => null,
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'component_id' => $component->id,
                'option_type' => OptionValueConstants::INTEGER,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_integers', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeDecimalNullable()
    {
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DECIMAL,
            'option_value' => null,
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'component_id' => $component->id,
                'option_type' => OptionValueConstants::DECIMAL,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_decimals', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }

    public function testStoreTypeDateNullable()
    {
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::DATE,
            'option_value' => null,
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'component_id' => $component->id,
                'option_type' => OptionValueConstants::DATE,
                'option_value' => $params['option_value'],
                'element_type' => OptionElementTypeConstants::TEXTBOX
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_dates', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => OptionElementTypeConstants::TEXTBOX
        ]);
    }
}
