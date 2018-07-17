<?php

namespace Tests\CmsApis;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\Component;
use App\Api\Models\ComponentOption;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\Language;
use App\Api\Models\Page;
use App\Api\Models\PageItem;
use App\Api\Models\PageItemOption;
use App\Api\Models\Site;
use App\Api\Models\Template;
use App\Api\Models\TemplateItem;
use App\Api\Models\TemplateItemOption;
use Tests\CmsApiTestCase;

class SiteTranslation extends CmsApiTestCase
{
    /**
     * @var Site
     */
    private $site;

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
     * @var Page
     */
    private $page;

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

        $this->site = factory(Site::class)->create(['is_active' => true]);

        $this->english = Language::firstOrCreate([
            'code' => 'en',
            'name' => 'English'
        ]);

        $this->thai = factory(Language::class)->create([
            'code' => 'th',
            'name' => 'Thailand'
        ]);

        $this->site->languages()->save($this->english, ['is_main' => true]);
        $this->site->languages()->save($this->thai);

        $this->template = factory(Template::class)->create(['site_id' => $this->site->id]);
        $this->page = factory(Page::class)->create(['template_id' => $this->template->id]);
        $this->globalItem = factory(GlobalItem::class)->create(['site_id' => $this->site->id]);
    }

    /**
     * Component Option
     */

    //Query
    //All Languages
    public function testGetAllComponentOptionsWithAllTranslations()
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

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/componentOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
//                        'language_code' => $this->english->code,
                        'translated_text' => 'TEST',
                    ]
                ]
            ])
            ->assertJsonFragment([
//                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
//                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);
    }

    public function testGetComponentOptionByIdWithAllTranslations()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/componentOption/' . $option->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
//                    'language_code' => $this->english->code,
                    'translated_text' => 'TEST'
                ]
            ])
            ->assertJsonFragment([
//                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
//                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);
    }

    //Create
    //Main Language
    public function testStoreComponentOptionWithMainLanguage()
    {
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $this->english->code
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
                'component_id' => $params['component_id'],
                'option_type' => $params['option_type'],
                'option_value' => $params['option_value'],
                'element_type' => $params['element_type'],
                'language_code' => $this->english->code,
                'translated_text' => $params['option_value']
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $params['component_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => $params['element_type'],
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
//            'translated_text' => $params['option_value']
            'translated_text' => null
        ]);
    }

    //Site Language
    public function testStoreComponentOptionTypeWithOtherLanguage()
    {
        $component = factory(Component::class)->create();

        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
                'component_id' => $params['component_id'],
                'option_type' => $params['option_type'],
                'option_value' => $params['option_value'],
                'element_type' => $params['element_type'],
                'language_code' => $params['language_code'],
                'translated_text' => $params['translated_text']
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $params['component_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Unknown Language
    public function testStoreComponentOptionTypeWithUnknownLanguage()
    {
        $component = factory(Component::class)->create();

        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => 'fr',
            'translated_text' => 'FRENCH TEXT'
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
            'component_id' => $params['component_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Non-site Language
    public function testStoreComponentOptionTypeWithNonSiteLanguage()
    {
        $component = factory(Component::class)->create();
        $nonSiteLanguage = factory(Language::class)->create();

        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $nonSiteLanguage->code,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
                'component_id' => $params['component_id'],
                'option_type' => $params['option_type'],
                'option_value' => $params['option_value'],
                'element_type' => $params['element_type'],
                'language_code' => $params['language_code'],
                'translated_text' => $params['translated_text']
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $params['component_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Without Language Code but has Translated Text
    public function testStoreComponentOptionWithoutLanguageCodeWhenTranslatedTextIsPresent()
    {
        $component = factory(Component::class)->create();
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'component_id' => $component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
                'translated_text' => $params['option_value']
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $params['component_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new ComponentOption),
            'element_type' => $params['element_type'],
        ]);
    }

    //Update
    //Update same language + same translated text
    public function testUpdateComponentOptionsWithSameLanguageAndSameTranslatedText()
    {
        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var ComponentOption $item */
            return $item->withOptionSiteTranslation($this->thai->code);
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }
    
    public function testUpdateComponentOptionByIdWithSameLanguageAndSameTranslatedText()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update same language + new translated text
    public function testUpdateComponentOptionsWithSameLanguageAndNewTranslatedText()
    {
        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var ComponentOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['translated_text'] = 'UPDATE เทส';
            $item['language_code'] = $this->thai->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'UPDATE เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'UPDATE เทส'
        ]);
    }
    
    public function testUpdateComponentOptionByIdWithSameLanguageAndNewTranslatedText()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $option['translated_text'] = 'UPDATE เทส';
        $option['language_code'] = $this->thai->code;
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'UPDATE เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'UPDATE เทส'
        ]);
    }

    //Update same language + no translated text
    public function testUpdateComponentOptionsWithSameLanguageAndNoTranslatedText()
    {
        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var ComponentOption $item */
            return collect($item->withOptionSiteTranslation($this->thai->code))->except('translated_text')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateComponentOptionByIdWithSameLanguageAndNoTranslatedText()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $data = collect($option->withOptionSiteTranslation($this->thai->code))->except('translated_text')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update new site language + same translated text
    public function testUpdateComponentOptionsWithNewSiteLanguageAndSameTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var ComponentOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['language_code'] = $language->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'TEST'
        ]);
    }
    
    public function testUpdateComponentOptionByIdWithNewSiteLanguageAndSameTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'TEST'
        ]);
    }

    //Update new site language + new translated text
    public function testUpdateComponentOptionsWithNewSiteLanguageAndNewTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var ComponentOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            $item['translated_text'] = 'NEW LANGUAGE TEXT';
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'NEW LANGUAGE TEXT'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'NEW LANGUAGE TEXT'
        ]);
    }

    public function testUpdateComponentOptionByIdWithNewSiteLanguageAndNewTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->thai->code);
        $optionWithTranslation['language_code'] = $language->code;
        $optionWithTranslation['translated_text'] = 'NEW LANGUAGE TEXT';
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'NEW LANGUAGE TEXT'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'NEW LANGUAGE TEXT'
        ]);
    }

    //Update new site language + no translated text
    public function testUpdateComponentOptionsWithNewSiteLanguageAndNoTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var ComponentOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            return collect($item)->except('translated_text')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateComponentOptionByIdWithNewSiteLanguageAndNoTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->thai->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = collect($optionWithTranslation)->except('translated_text')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update no language + same translated text
    public function testUpdateComponentOptionsWithNoSiteLanguageAndSameTranslatedText()
    {
        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var ComponentOption $item */
            return collect($item->withOptionSiteTranslation($this->english->code))->except('language_code')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateComponentOptionByIdWithNoSiteLanguageAndSameTranslatedText()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $data = collect($optionWithTranslation)->except('language_code')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update no language + new translated text
    public function testUpdateComponentOptionsWithNoSiteLanguageAndNewTranslatedText()
    {
        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var ComponentOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['translated_text'] = 'UPDATE TEXT';
            return collect($item)->except('language_code')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'translated_text' => 'UPDATE TEXT'
        ]);
    }

    public function testUpdateComponentOptionByIdWithNoSiteLanguageAndNewTranslatedText()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['translated_text'] = 'UPDATE TEXT';
        $data = collect($optionWithTranslation)->except('language_code')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'translated_text' => 'UPDATE TEXT'
        ]);
    }

    //Update no language + no translated text
    public function testUpdateComponentOptionsWithNoSiteLanguageAndNoTranslatedText()
    {
        $component = factory(Component::class)->create();

        /** @var ComponentOption[]|\Illuminate\Support\Collection $options */
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateComponentOptionByIdWithNoSiteLanguageAndNoTranslatedText()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to unknown language
    public function testUpdateComponentOptionsWithUnknownLanguage()
    {
        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var ComponentOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = 'unknownlang';
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateComponentOptionByIdWithUnknownLanguage()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $option['language_code'] = 'unknownlang';
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to non-site language
    public function testUpdateComponentOptionsWithNonSiteLanguage()
    {
        $language = factory(Language::class)->create();

        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var ComponentOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateComponentOptionByIdWithNonSiteLanguage()
    {
        $language = factory(Language::class)->create();

        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to main language
    public function testUpdateComponentOptionsWithMainSiteLanguage()
    {
        $component = factory(Component::class)->create();
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var ComponentOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var ComponentOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['language_code'] = $this->english->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateComponentOptionByIdWithMainSiteLanguage()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $this->english->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Delete
    //Cascade delete site translations
    public function testDeleteComponentOptionCascadeDeleteSiteTranslations()
    {
        $component = factory(Component::class)->create();

        /** @var ComponentOption[]|\Illuminate\Support\Collection $options */
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $data = $options->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/componentOptions', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('component_options', [
            'component_id' => $component->id
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testDeleteComponentOptionByIdCascadeDeleteSiteTranslation()
    {
        $component = factory(Component::class)->create();
        $option = factory(ComponentOption::class)->create(['component_id' => $component->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/componentOption/' . $option->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('component_options', [
            'component_id' => $component->id
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    /**
     * Template Item Option
     */
    
    //Query
    //All Languages
    public function testGetAllTemplateItemOptionsWithAllTranslations()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/templateItemOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);
    }

    public function testGetTemplateItemOptionByIdWithAllTranslations()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/templateItemOption/' . $option->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);
    }

    //Create
    //Main Language
    public function testStoreTemplateItemOptionWithMainLanguage()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'template_item_id' => $templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $this->english->code,
            'translated_text' => self::$faker->sentence()
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
                'template_item_id' => $params['template_item_id'],
                'option_type' => $params['option_type'],
                'option_value' => $params['option_value'],
                'element_type' => $params['element_type'],
                'language_code' => $this->english->code,
                'translated_text' => $params['translated_text']
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $params['template_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => $params['element_type'],
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => $params['translated_text']
        ]);
    }

    //Site Language
    public function testStoreTemplateItemOptionTypeWithOtherLanguage()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'template_item_id' => $templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
                'template_item_id' => $params['template_item_id'],
                'option_type' => $params['option_type'],
                'option_value' => $params['option_value'],
                'element_type' => $params['element_type'],
                'language_code' => $params['language_code'],
                'translated_text' => $params['translated_text']
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $params['template_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Unknown Language
    public function testStoreTemplateItemOptionTypeWithUnknownLanguage()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'template_item_id' => $templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => 'fr',
            'translated_text' => 'FRENCH TEXT'
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
            'template_item_id' => $params['template_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Non-site Language
    public function testStoreTemplateItemOptionTypeWithNonSiteLanguage()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $nonSiteLanguage = factory(Language::class)->create();

        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'template_item_id' => $templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $nonSiteLanguage->code,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
            'template_item_id' => $params['template_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Without Language Code but has Translated Text
    public function testStoreTemplateItemOptionWithoutLanguageCodeWhenTranslatedTextIsPresent()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'template_item_id' => $templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
                'translated_text' => $params['option_value']
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $params['template_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new TemplateItemOption),
            'element_type' => $params['element_type'],
        ]);
    }

    //Update
    //Update same language + same translated text
    public function testUpdateTemplateItemOptionsWithSameLanguageAndSameTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var TemplateItemOption $item */
            return $item->withOptionSiteTranslation($this->thai->code);
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithSameLanguageAndSameTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update same language + new translated text
    public function testUpdateTemplateItemOptionsWithSameLanguageAndNewTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var TemplateItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['translated_text'] = 'UPDATE เทส';
            $item['language_code'] = $this->thai->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'UPDATE เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'UPDATE เทส'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithSameLanguageAndNewTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $option['translated_text'] = 'UPDATE เทส';
        $option['language_code'] = $this->thai->code;
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'UPDATE เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'UPDATE เทส'
        ]);
    }

    //Update same language + no translated text
    public function testUpdateTemplateItemOptionsWithSameLanguageAndNoTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var TemplateItemOption $item */
            return collect($item->withOptionSiteTranslation($this->thai->code))->except('translated_text')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithSameLanguageAndNoTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $data = collect($option->withOptionSiteTranslation($this->thai->code))->except('translated_text')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update new site language + same translated text
    public function testUpdateTemplateItemOptionsWithNewSiteLanguageAndSameTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var TemplateItemOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['language_code'] = $language->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'TEST'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithNewSiteLanguageAndSameTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'TEST'
        ]);
    }

    //Update new site language + new translated text
    public function testUpdateTemplateItemOptionsWithNewSiteLanguageAndNewTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var TemplateItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            $item['translated_text'] = 'NEW LANGUAGE TEXT';
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'NEW LANGUAGE TEXT'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'NEW LANGUAGE TEXT'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithNewSiteLanguageAndNewTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->thai->code);
        $optionWithTranslation['language_code'] = $language->code;
        $optionWithTranslation['translated_text'] = 'NEW LANGUAGE TEXT';
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'NEW LANGUAGE TEXT'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'NEW LANGUAGE TEXT'
        ]);
    }

    //Update new site language + no translated text
    public function testUpdateTemplateItemOptionsWithNewSiteLanguageAndNoTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var TemplateItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            return collect($item)->except('translated_text')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithNewSiteLanguageAndNoTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->thai->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = collect($optionWithTranslation)->except('translated_text')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update no language + same translated text
    public function testUpdateTemplateItemOptionsWithNoSiteLanguageAndSameTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var TemplateItemOption $item */
            return collect($item->withOptionSiteTranslation($this->english->code))->except('language_code')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithNoSiteLanguageAndSameTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $data = collect($optionWithTranslation)->except('language_code')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update no language + new translated text
    public function testUpdateTemplateItemOptionsWithNoSiteLanguageAndNewTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var TemplateItemOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['translated_text'] = 'UPDATE TEXT';
            return collect($item)->except('language_code')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'translated_text' => 'UPDATE TEXT'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithNoSiteLanguageAndNewTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['translated_text'] = 'UPDATE TEXT';
        $data = collect($optionWithTranslation)->except('language_code')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'translated_text' => 'UPDATE TEXT'
        ]);
    }

    //Update no language + no translated text
    public function testUpdateTemplateItemOptionsWithNoSiteLanguageAndNoTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithNoSiteLanguageAndNoTranslatedText()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to unknown language
    public function testUpdateTemplateItemOptionsWithUnknownLanguage()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var TemplateItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = 'unknownlang';
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithUnknownLanguage()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $option['language_code'] = 'unknownlang';
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to non-site language
    public function testUpdateTemplateItemOptionsWithNonSiteLanguage()
    {
        $language = factory(Language::class)->create();

        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var TemplateItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithNonSiteLanguage()
    {
        $language = factory(Language::class)->create();

        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to main language
    public function testUpdateTemplateItemOptionsWithMainSiteLanguage()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var TemplateItemOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['language_code'] = $this->english->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateTemplateItemOptionByIdWithMainSiteLanguage()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $this->english->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Delete
    //Cascade delete site translations
    public function testDeleteTemplateItemOptionCascadeDeleteSiteTranslations()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $data = $options->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/templateItemOptions', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('template_item_options', [
            'template_item_id' => $templateItem->id
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testDeleteTemplateItemOptionByIdCascadeDeleteSiteTranslation()
    {
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $templateItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/templateItemOption/' . $option->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('template_item_options', [
            'template_item_id' => $templateItem->id
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    /**
     * Page Item Option
     */

    //Query
    //All Languages
    public function testGetAllPageItemOptionsWithAllTranslations()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/pageItemOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);
    }

    public function testGetPageItemOptionByIdWithAllTranslations()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/pageItemOption/' . $option->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);
    }

    //Create
    //Main Language
    public function testStorePageItemOptionWithMainLanguage()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'page_item_id' => $pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $this->english->code,
            'translated_text' => self::$faker->sentence(),
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
                'page_item_id' => $params['page_item_id'],
                'option_type' => $params['option_type'],
                'option_value' => $params['option_value'],
                'element_type' => $params['element_type'],
                'language_code' => $this->english->code,
                'translated_text' => $params['translated_text']
            ]);

        $this->assertDatabaseHas('page_item_options', [
            'page_item_id' => $params['page_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => $params['element_type'],
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => $params['translated_text']
        ]);
    }

    //Site Language
    public function testStorePageItemOptionTypeWithOtherLanguage()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'page_item_id' => $pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
                'page_item_id' => $params['page_item_id'],
                'option_type' => $params['option_type'],
                'option_value' => $params['option_value'],
                'element_type' => $params['element_type'],
                'language_code' => $params['language_code'],
                'translated_text' => $params['translated_text']
            ]);

        $this->assertDatabaseHas('page_item_options', [
            'page_item_id' => $params['page_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Unknown Language
    public function testStorePageItemOptionTypeWithUnknownLanguage()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'page_item_id' => $pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => 'fr',
            'translated_text' => 'FRENCH TEXT'
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
            'page_item_id' => $params['page_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Non-site Language
    public function testStorePageItemOptionTypeWithNonSiteLanguage()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $nonSiteLanguage = factory(Language::class)->create();

        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'page_item_id' => $pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $nonSiteLanguage->code,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
            'page_item_id' => $params['page_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Without Language Code but has Translated Text
    public function testStorePageItemOptionWithoutLanguageCodeWhenTranslatedTextIsPresent()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'page_item_id' => $pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
                'translated_text' => $params['option_value']
            ]);

        $this->assertDatabaseHas('page_item_options', [
            'page_item_id' => $params['page_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new PageItemOption),
            'element_type' => $params['element_type'],
        ]);
    }

    //Update
    //Update same language + same translated text
    public function testUpdatePageItemOptionsWithSameLanguageAndSameTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var PageItemOption $item */
            return $item->withOptionSiteTranslation($this->thai->code);
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithSameLanguageAndSameTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update same language + new translated text
    public function testUpdatePageItemOptionsWithSameLanguageAndNewTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var PageItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['translated_text'] = 'UPDATE เทส';
            $item['language_code'] = $this->thai->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'UPDATE เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'UPDATE เทส'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithSameLanguageAndNewTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $option['translated_text'] = 'UPDATE เทส';
        $option['language_code'] = $this->thai->code;
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'UPDATE เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'UPDATE เทส'
        ]);
    }

    //Update same language + no translated text
    public function testUpdatePageItemOptionsWithSameLanguageAndNoTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var PageItemOption $item */
            return collect($item->withOptionSiteTranslation($this->thai->code))->except('translated_text')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'เทส'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithSameLanguageAndNoTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $data = collect($option->withOptionSiteTranslation($this->thai->code))->except('translated_text')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update new site language + same translated text
    public function testUpdatePageItemOptionsWithNewSiteLanguageAndSameTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var PageItemOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['language_code'] = $language->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithNewSiteLanguageAndSameTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update new site language + new translated text
    public function testUpdatePageItemOptionsWithNewSiteLanguageAndNewTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var PageItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            $item['translated_text'] = 'NEW LANGUAGE TEXT';
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'NEW LANGUAGE TEXT'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'NEW LANGUAGE TEXT'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithNewSiteLanguageAndNewTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->thai->code);
        $optionWithTranslation['language_code'] = $language->code;
        $optionWithTranslation['translated_text'] = 'NEW LANGUAGE TEXT';
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'NEW LANGUAGE TEXT'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'NEW LANGUAGE TEXT'
        ]);
    }

    //Update new site language + no translated text
    public function testUpdatePageItemOptionsWithNewSiteLanguageAndNoTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var PageItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            return collect($item)->except('translated_text')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithNewSiteLanguageAndNoTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->thai->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = collect($optionWithTranslation)->except('translated_text')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update no language + same translated text
    public function testUpdatePageItemOptionsWithNoSiteLanguageAndSameTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var PageItemOption $item */
            return collect($item->withOptionSiteTranslation($this->english->code))->except('language_code')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithNoSiteLanguageAndSameTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $data = collect($optionWithTranslation)->except('language_code')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update no language + new translated text
    public function testUpdatePageItemOptionsWithNoSiteLanguageAndNewTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var PageItemOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['translated_text'] = 'UPDATE TEXT';
            return collect($item)->except('language_code')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'translated_text' => 'UPDATE TEXT'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithNoSiteLanguageAndNewTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['translated_text'] = 'UPDATE TEXT';
        $data = collect($optionWithTranslation)->except('language_code')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'translated_text' => 'UPDATE TEXT'
        ]);
    }

    //Update no language + no translated text
    public function testUpdatePageItemOptionsWithNoSiteLanguageAndNoTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);

        /** @var PageItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithNoSiteLanguageAndNoTranslatedText()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to unknown language
    public function testUpdatePageItemOptionsWithUnknownLanguage()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var PageItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = 'unknownlang';
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithUnknownLanguage()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $option['language_code'] = 'unknownlang';
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to non-site language
    public function testUpdatePageItemOptionsWithNonSiteLanguage()
    {
        $language = factory(Language::class)->create();

        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var PageItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithNonSiteLanguage()
    {
        $language = factory(Language::class)->create();

        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to main language
    public function testUpdatePageItemOptionsWithMainSiteLanguage()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var PageItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var PageItemOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['language_code'] = $this->english->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdatePageItemOptionByIdWithMainSiteLanguage()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $this->english->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Delete
    //Cascade delete site translations
    public function testDeletePageItemOptionCascadeDeleteSiteTranslations()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);

        /** @var PageItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $data = $options->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/pageItemOptions', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('page_item_options', [
            'page_item_id' => $pageItem->id
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testDeletePageItemOptionByIdCascadeDeleteSiteTranslation()
    {
        $pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/pageItemOption/' . $option->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('page_item_options', [
            'page_item_id' => $pageItem->id
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    /**
     * Global Item Option
     */
    
    //Query
    //All Languages
    public function testGetAllGlobalItemOptionsWithAllTranslations()
    {
        factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/globalItemOptions', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
//                        'language_code' => $this->english->code,
                        'translated_text' => 'TEST'
                    ]
                ]
            ])
            ->assertJsonFragment([
//                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
//                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);
    }

    public function testGetGlobalItemOptionByIdWithAllTranslations()
    {
        
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/globalItemOption/' . $option->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
//                    'language_code' => $this->english->code,
                    'translated_text' => 'TEST'
                ]
            ])
            ->assertJsonFragment([
//                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
//                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);
    }

    //Create
    //Main Language
    public function testStoreGlobalItemOptionWithMainLanguage()
    {
        
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $this->english->code,
            'translated_text' => self::$faker->sentence(),
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
                'global_item_id' => $params['global_item_id'],
                'option_type' => $params['option_type'],
                'option_value' => $params['option_value'],
                'element_type' => $params['element_type'],
                'language_code' => $this->english->code,
                'translated_text' => $params['translated_text']
            ]);

        $this->assertDatabaseHas('global_item_options', [
            'global_item_id' => $params['global_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => $params['element_type'],
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => $params['translated_text']
        ]);
    }

    //Site Language
    public function testStoreGlobalItemOptionTypeWithOtherLanguage()
    {
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
                'global_item_id' => $params['global_item_id'],
                'option_type' => $params['option_type'],
                'option_value' => $params['option_value'],
                'element_type' => $params['element_type'],
                'language_code' => $params['language_code'],
                'translated_text' => $params['translated_text']
            ]);

        $this->assertDatabaseHas('global_item_options', [
            'global_item_id' => $params['global_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Unknown Language
    public function testStoreGlobalItemOptionTypeWithUnknownLanguage()
    {
        
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => 'fr',
            'translated_text' => 'FRENCH TEXT'
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
            'global_item_id' => $params['global_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Non-site Language
    public function testStoreGlobalItemOptionTypeWithNonSiteLanguage()
    {
        
        $nonSiteLanguage = factory(Language::class)->create();

        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'language_code' => $nonSiteLanguage->code,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
            'global_item_id' => $params['global_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseMissing('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => $params['element_type']
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $params['language_code'],
            'translated_text' => $params['translated_text']
        ]);
    }

    //Without Language Code but has Translated Text
    public function testStoreGlobalItemOptionWithoutLanguageCodeWhenTranslatedTextIsPresent()
    {
        
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => self::$faker->sentence(),
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'translated_text' => 'เทส คอมโพเนเท์ ออพชั่น'
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
                'translated_text' => $params['option_value']
            ]);
        
        $this->assertDatabaseHas('global_item_options', [
            'global_item_id' => $params['global_item_id'],
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $params['option_value']
        ]);

        $this->assertDatabaseHas('option_element_types', [
            'item_type' => class_basename(new GlobalItemOption),
            'element_type' => $params['element_type'],
        ]);
    }

    //Update
    //Update same language + same translated text
    public function testUpdateGlobalItemOptionsWithSameLanguageAndSameTranslatedText()
    {
        
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var GlobalItemOption $item */
            return $item->withOptionSiteTranslation($this->thai->code);
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithSameLanguageAndSameTranslatedText()
    {
        
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update same language + new translated text
    public function testUpdateGlobalItemOptionsWithSameLanguageAndNewTranslatedText()
    {
        
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var GlobalItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['translated_text'] = 'UPDATE เทส';
            $item['language_code'] = $this->thai->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'UPDATE เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'UPDATE เทส'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithSameLanguageAndNewTranslatedText()
    {
        
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $option['translated_text'] = 'UPDATE เทส';
        $option['language_code'] = $this->thai->code;
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'UPDATE เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'UPDATE เทส'
        ]);
    }

    //Update same language + no translated text
    public function testUpdateGlobalItemOptionsWithSameLanguageAndNoTranslatedText()
    {
        
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var GlobalItemOption $item */
            return collect($item->withOptionSiteTranslation($this->thai->code))->except('translated_text')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithSameLanguageAndNoTranslatedText()
    {
        
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $data = collect($option->withOptionSiteTranslation($this->thai->code))->except('translated_text')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update new site language + same translated text
    public function testUpdateGlobalItemOptionsWithNewSiteLanguageAndSameTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var GlobalItemOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['language_code'] = $language->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithNewSiteLanguageAndSameTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update new site language + new translated text
    public function testUpdateGlobalItemOptionsWithNewSiteLanguageAndNewTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var GlobalItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            $item['translated_text'] = 'NEW LANGUAGE TEXT';
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'NEW LANGUAGE TEXT'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'NEW LANGUAGE TEXT'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithNewSiteLanguageAndNewTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->thai->code);
        $optionWithTranslation['language_code'] = $language->code;
        $optionWithTranslation['translated_text'] = 'NEW LANGUAGE TEXT';
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ])
            ->assertJsonFragment([
                'language_code' => $language->code,
                'translated_text' => 'NEW LANGUAGE TEXT'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'NEW LANGUAGE TEXT'
        ]);
    }

    //Update new site language + no translated text
    public function testUpdateGlobalItemOptionsWithNewSiteLanguageAndNoTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var GlobalItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            return collect($item)->except('translated_text')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithNewSiteLanguageAndNoTranslatedText()
    {
        $language = factory(Language::class)->create();
        $this->site->languages()->save($language);

        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->thai->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = collect($optionWithTranslation)->except('translated_text')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update no language + same translated text
    public function testUpdateGlobalItemOptionsWithNoSiteLanguageAndSameTranslatedText()
    {
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var GlobalItemOption $item */
            return collect($item->withOptionSiteTranslation($this->english->code))->except('language_code')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithNoSiteLanguageAndSameTranslatedText()
    {
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $data = collect($optionWithTranslation)->except('language_code')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update no language + new translated text
    public function testUpdateGlobalItemOptionsWithNoSiteLanguageAndNewTranslatedText()
    {
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var GlobalItemOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['translated_text'] = 'UPDATE TEXT';
            return collect($item)->except('language_code')->all();
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'translated_text' => 'UPDATE TEXT'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithNoSiteLanguageAndNewTranslatedText()
    {
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['translated_text'] = 'UPDATE TEXT';
        $data = collect($optionWithTranslation)->except('language_code')->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'translated_text' => 'UPDATE TEXT'
        ]);
    }

    //Update no language + no translated text
    public function testUpdateGlobalItemOptionsWithNoSiteLanguageAndNoTranslatedText()
    {
        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $data = $options->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithNoSiteLanguageAndNoTranslatedText()
    {
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to unknown language
    public function testUpdateGlobalItemOptionsWithUnknownLanguage()
    {
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var GlobalItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = 'unknownlang';
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithUnknownLanguage()
    {
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $option->withOptionSiteTranslation($this->thai->code);
        $option['language_code'] = 'unknownlang';
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to non-site language
    public function testUpdateGlobalItemOptionsWithNonSiteLanguage()
    {
        $language = factory(Language::class)->create();

        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) use ($language) {
            /** @var GlobalItemOption $item */
            $item = $item->withOptionSiteTranslation($this->thai->code);
            $item['language_code'] = $language->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOptions/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithNonSiteLanguage()
    {
        $language = factory(Language::class)->create();

        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $language->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Update any language to main language
    public function testUpdateGlobalItemOptionsWithMainSiteLanguage()
    {
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $optionsWithTranslations */
        $optionsWithTranslations = $options;
        $optionsWithTranslations->transform(function ($item) {
            /** @var GlobalItemOption $item */
            $item = $item->withOptionSiteTranslation($this->english->code);
            $item['language_code'] = $this->english->code;
            return $item;
        });
        $data = $optionsWithTranslations->toArray();

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
                        'site_translations' => [
                            [
                                'language_code' => $this->english->code
                            ],
                            [
                                'language_code' => $this->thai->code
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testUpdateGlobalItemOptionByIdWithMainSiteLanguage()
    {
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $optionWithTranslation = $option->withOptionSiteTranslation($this->english->code);
        $optionWithTranslation['language_code'] = $this->english->code;
        $data = $optionWithTranslation;

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
            ->assertJsonFragment([
                'language_code' => $this->thai->code,
                'translated_text' => 'เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $this->english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    //Delete
    //Cascade delete site translations
    public function testDeleteGlobalItemOptionCascadeDeleteSiteTranslations()
    {
        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
            $item->upsertOptionSiteTranslation($this->english->code, 'TEST');
            $item->upsertOptionSiteTranslation($this->thai->code, 'เทส');
        });

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $data = $options->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/globalItemOptions', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('global_item_options', [
            'global_item_id' => $this->globalItem->id
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testDeleteGlobalItemOptionByIdCascadeDeleteSiteTranslation()
    {
        $globalItem = factory(GlobalItem::class)->create(['site_id' => $this->site->id]);
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $globalItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($this->english->code, 'TEST');
        $option->upsertOptionSiteTranslation($this->thai->code, 'เทส');

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/globalItemOption/' . $option->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('global_item_options', [
            'global_item_id' => $globalItem->id
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $this->thai->code,
            'translated_text' => 'เทส'
        ]);
    }
}
