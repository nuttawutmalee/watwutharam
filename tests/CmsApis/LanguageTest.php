<?php

namespace Tests\CmsApis;

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

class LanguageTest extends CmsApiTestCase
{
    public function testGetAllLanguages()
    {
        factory(Language::class, 3)->create();
        $languages = Language::all();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/languages', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $languages->toArray()
            ]);
    }

    public function testGetLanguageByCode()
    {
        $language = factory(Language::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/language/' . $language->code, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'code' => $language->code
                ]
            ]);
    }

    public function testStore()
    {
        $params = [
            'code' => 'fr',
            'name' => 'French',
            'is_active' => false
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/language', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'code' => $params['code'],
                    'name' => $params['name'],
                    'is_active' => $params['is_active']
                ]
            ]);

        $this->assertDatabaseHas('languages', [
            'code' => $params['code'],
            'name' => $params['name'],
            'is_active' => $params['is_active']
        ]);
    }

    public function testUpdate()
    {
        /** @var Language[]|\Illuminate\Support\Collection $languages */
        $languages = factory(Language::class, 3)->create([
            'is_active' => false
        ])->each(function ($language) {
            $language->is_active = true;
        });
        $data = $languages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/languages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'is_active' => true
                    ]
                ]
            ]);

        $this->assertDatabaseHas('languages', [
            'code' => $languages->first()->code,
            'is_active' => true
        ]);
    }

    public function testUpdateByCode()
    {
        $language = factory(Language::class)->create([
            'is_active' => false
        ]);
        $language->is_active = true;
        $data = $language->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/language/' . $language->code . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'is_active' => true
                ]
            ]);

        $this->assertDatabaseHas('languages', [
            'code' => $language->code,
            'is_active' => true
        ]);
    }

    public function testDelete()
    {
        $languages = factory(Language::class, 3)->create();
        $data = $languages->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/languages', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('languages', [
            'code' => $languages->first()->code
        ]);
    }

    public function testDeleteByCode()
    {
        $language = factory(Language::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/language/'. $language->code, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('languages', [
            'code' => $language->code
        ]);
    }

    //Integrations
    public function testSiteLanguageCascadeDelete()
    {
        $site = factory(Site::class)->create();
        $language = factory(Language::class)->create();
        $site->languages()->save($language);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/language/'. $language->code, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('languages', [
            'code' => $language->code
        ]);

        $this->assertDatabaseMissing('site_languages', [
            'language_code' => $language->code,
            'site_id' => $site->id
        ]);

        $this->assertDatabaseHas('sites', [
            'id' => $site->id
        ]);
    }

    public function testSiteLanguageCascadeActiveUpdate()
    {
        $language = factory(Language::class)->create();
        factory(Site::class, 3)
            ->create()
            ->each(function ($site) use ($language) {
                /** @var Site $site */
                $site->languages()->save($language, ['is_active' => true]);
            });
        $language->is_active = false;
        $data = $language->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/language/'. $language->code . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'is_active' => false
                ]
            ]);

        $this->assertDatabaseHas('languages', [
            'code' => $language->code,
            'is_active' => 0
        ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $language->code,
            'is_active' => 1
        ]);
    }

    public function testSiteTranslationComponentOptionCascadeDelete()
    {
        $language = factory(Language::class)->create();

        $component = factory(Component::class)->create();
        factory(ComponentOption::class, 3)->create([
            'component_id' => $component->id
        ])->each(function ($item) use ($language) {
            /** @var ComponentOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionSiteTranslation($language->code, 'เทส');
        });

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/language/'. $language->code, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $language->code,
            'translated_text' => 'เทส'
        ]);
    }

    public function testSiteTranslationTemplateItemOptionCascadeDelete()
    {
        $site = factory(Site::class)->create();

        /** @var Language $english */
        $english = Language::firstOrCreate([
            'code' => 'en',
            'name' => 'English'
        ]);

        /** @var Language $thai */
        $thai = factory(Language::class)->create([
            'code' => 'th',
            'name' => 'Thailand'
        ]);
        $site->languages()->save($english, ['is_main' => true]);
        $site->languages()->save($thai);


        $template = factory(Template::class)->create(['site_id' => $site->id]);
        $templateItem = factory(TemplateItem::class)->create(['template_id' => $template->id]);
        factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $templateItem->id
        ])->each(function ($item) use ($thai, $english) {
            /** @var TemplateItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionSiteTranslation($english->code, 'TEST');
            $item->upsertOptionSiteTranslation($thai->code, 'เทส');
        });

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/language/'. $thai->code, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $thai->code,
            'translated_text' => 'เทส'
        ]);
    }
    
    public function testSiteTranslationPageItemOptionCascadeDelete()
    {
        $site = factory(Site::class)->create();

        /** @var Language $english */
        $english = Language::firstOrCreate([
            'code' => 'en',
            'name' => 'English'
        ]);

        /** @var Language $thai */
        $thai = factory(Language::class)->create([
            'code' => 'th',
            'name' => 'Thailand'
        ]);
        $site->languages()->save($english, ['is_main' => true]);
        $site->languages()->save($thai);


        $template = factory(Template::class)->create(['site_id' => $site->id]);
        $page = factory(Page::class)->create(['template_id' => $template->id]);
        $pageItem = factory(PageItem::class)->create(['page_id' => $page->id]);
        factory(PageItemOption::class, 3)->create([
            'page_item_id' => $pageItem->id
        ])->each(function ($item) use ($thai, $english) {
            /** @var PageItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionSiteTranslation($english->code, 'TEST');
            $item->upsertOptionSiteTranslation($thai->code, 'เทส');
        });

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/language/'. $thai->code, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $thai->code,
            'translated_text' => 'เทส'
        ]);
    }
    
    public function testSiteTranslationGlobalItemOptionCascadeDelete()
    {
        $site = factory(Site::class)->create();

        /** @var Language $english */
        $english = Language::firstOrCreate([
            'code' => 'en',
            'name' => 'English'
        ]);

        /** @var Language $thai */
        $thai = factory(Language::class)->create([
            'code' => 'th',
            'name' => 'Thailand'
        ]);
        $site->languages()->save($english, ['is_main' => true]);
        $site->languages()->save($thai);


        $globalItem = factory(GlobalItem::class)->create(['site_id' => $site->id]);
        factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $globalItem->id
        ])->each(function ($item) use ($thai, $english) {
            /** @var GlobalItemOption $item */
            $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
            $item->upsertOptionSiteTranslation($english->code, 'TEST');
            $item->upsertOptionSiteTranslation($thai->code, 'เทส');
        });

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/language/'. $thai->code, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $thai->code,
            'translated_text' => 'เทส'
        ]);
    }
}
