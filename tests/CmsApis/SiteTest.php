<?php

namespace Tests\CmsApis;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\Language;
use App\Api\Models\Page;
use App\Api\Models\RedirectUrl;
use App\Api\Models\Site;
use App\Api\Models\Template;
use Tests\CmsApiTestCase;

class SiteTest extends CmsApiTestCase
{
    public function testGetAllSites()
    {
        factory(Site::class, 3)->create();
        $sites = Site::all()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/sites', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $sites
            ]);
    }

    public function testGetSiteById()
    {
        $site = factory(Site::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/site/' . $site->id, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'id' => $site->id
                ]
            ]);
    }

    public function testStore()
    {
        $params = [
            'domain_name' => self::$faker->unique()->domainName,
            'is_active' => true
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'result',
                'data' => [
                    'domain_name', 'is_active'
                ]
            ])
            ->assertJson([
                'result' => true,
                'data' => [
                    'domain_name' => $params['domain_name']
                ]
            ]);

        $this->assertDatabaseHas('sites', [
            'domain_name' => $params['domain_name']
        ]);
    }

    public function testUpdate()
    {
        $sites = factory(Site::class, 3)->create();
        $data = $sites->each(function ($item) {
            $item->is_active = false;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/sites/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'is_active' => false
                    ]
                ]
            ]);

        $this->assertDatabaseHas('sites', [
            'id' => $sites->first()->id,
            'is_active' => false
        ]);
    }

    public function testDelete()
    {
        $sites = factory(Site::class, 3)->create();
        $data = $sites->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/sites', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('sites', [
            'id' => $sites->first()->id
        ]);
    }

    public function testUpdateById()
    {
        $site = factory(Site::class)->create();
        $updatedDomainName = self::$faker->unique()->domainName;
        $site->domain_name = $updatedDomainName;
        $site->is_active = false;
        $data = $site->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'domain_name' => $updatedDomainName,
                    'is_active' => false
                ]
            ]);

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'domain_name' => $updatedDomainName,
            'is_active' => false
        ]);
    }

    public function testDeleteById()
    {
        $site = factory(Site::class)->create();

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/site/' . $site->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('sites', [
            'id' => $site->id
        ]);
    }

    //Integrations
    public function testGetRedirectUrls()
    {
        $site = factory(Site::class)->create();
        $urls = factory(RedirectUrl::class, 3)->create([
            'site_id' => $site->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/site/' . $site->id . '/redirectUrls', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    [
                        'id' => $urls->first()->id
                    ]
                ]
            ]);
    }

    public function testGetTemplates()
    {
        $site = factory(Site::class)->create();
        $templates = factory(Template::class, 3)->create([
            'site_id' => $site->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/site/' . $site->id . '/templates', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    [
                        'id' => $templates->first()->id
                    ]
                ]
            ]);
    }

    public function testGetGlobalItems()
    {
        $site = factory(Site::class)->create();
        $globalItems = factory(GlobalItem::class, 3)->create([
            'site_id' => $site->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/site/' . $site->id . '/globalItems', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    [
                        'id' => $globalItems->first()->id
                    ]
                ]
            ]);
    }

    public function testRedirectUrlCascadeDelete()
    {
        $site = factory(Site::class)->create();
        $urls = factory(RedirectUrl::class, 3)->create([
            'site_id' => $site->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/site/' . $site->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('sites', [
            'id' => $site->id
        ]);

        $this->assertDatabaseMissing('redirect_urls', [
            'id' => $urls->first()->id
        ]);
    }

    public function testTemplateCascadeDelete()
    {
        $site = factory(Site::class)->create();
        $templates = factory(Template::class, 3)->create([
            'site_id' => $site->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/site/' . $site->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('sites', [
            'id' => $site->id
        ]);

        $this->assertDatabaseMissing('templates', [
            'id' => $templates->first()->id
        ]);
    }

    public function testGlobalItemCascadeDelete()
    {
        $site = factory(Site::class)->create();
        $globalItems = factory(GlobalItem::class, 3)->create([
            'site_id' => $site->id
        ]);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/site/' . $site->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('sites', [
            'id' => $site->id
        ]);

        $this->assertDatabaseMissing('global_items', [
            'id' => $globalItems->first()->id
        ]);
    }

    public function testSiteLanguagesCascadeDelete()
    {
        $site = factory(Site::class)->create();
        $language = factory(Language::class)->create();
        $site->languages()->save($language);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/site/' . $site->id, [], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('sites', [
            'id' => $site->id
        ]);

        $this->assertDatabaseMissing('site_languages', [
            'site_id' => $site->id,
            'language_id' => $language->id
        ]);
    }

    //Languages
    public function testGetSiteLanguages()
    {
        $site = factory(Site::class)->create();
        $language = factory(Language::class)->create();
        $site->languages()->save($language);

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/site/' . $site->id . '/languages', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'code' => $language->code,
                        'name' => $language->name,
                        'is_active' => $language->is_active,
                        'pivot' => [
                            'language_code' => $language->code
                        ]
                    ]
                ]
            ]);
    }

    public function testGetSiteLanguageBySiteIdAndCode()
    {
        $site = factory(Site::class)->create();
        $language = factory(Language::class)->create();
        $site->languages()->save($language);

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/site/' . $site->id . '/language/' . $language->code, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'code' => $language->code,
                    'name' => $language->name,
                    'is_active' => $language->is_active,
                    'pivot' => [
                        'language_code' => $language->code
                    ]
                ]
            ]);
    }

    public function testAttachSiteLanguageBySiteId()
    {
        $site = factory(Site::class)->create();
        $language = factory(Language::class)->create();

        $params = [
            'language_code' => $language->code
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/language', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'code' => $language->code,
                    'pivot' => [
                        'language_code' => $language->code,
                        'site_id' => $site->id
                    ]
                ]
            ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $language->code,
            'site_id' => $site->id
        ]);
    }

    public function testAttachSiteLanguageBySiteIdChangeMainLanguage()
    {
        $site = factory(Site::class)->create();
        $language = factory(Language::class)->create();
        $site->languages()->save($language, ['is_main' => true, 'is_active' => false]);

        $anotherLanguage = factory(Language::class)->create();

        $params = [
            'language_code' => $anotherLanguage->code,
            'is_main' => true,
            'is_active' => false
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/language', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'code' => $anotherLanguage->code,
                    'pivot' => [
                        'language_code' => $anotherLanguage->code,
                        'site_id' => $site->id,
                        'is_main' => true,
                        'is_active' => true
                    ]
                ]
            ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $language->code,
            'site_id' => $site->id,
            'is_main' => false
        ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $anotherLanguage->code,
            'site_id' => $site->id,
            'is_main' => true
        ]);
    }

    public function testAttachLanguageBySiteIdErrorDuplicateLanguageCode()
    {
        $site = factory(Site::class)->create();
        $language = factory(Language::class)->create();
        $site->languages()->save($language, ['is_main' => true, 'is_active' => false]);

        $params = [
            'language_code' => $language->code,
            'is_main' => true,
            'is_active' => true
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/language', $params, $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('site_languages', [
            'language_code' => $language->code,
            'site_id' => $site->id,
            'is_active' => true
        ]);
    }

    public function testUpdateSiteLanguagesBySiteId()
    {
        $languages = factory(Language::class, 3)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->sync($languages, ['is_active' => true]);

        $data = $site->languages()->get()->each(function ($language) {
            $language->pivot->is_active = false;
        })->map(function ($language) {
            return $language->pivot;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/languages/update', ['data' => $data], $header);

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

    public function testUpdateSiteLanguagesBySiteIdChangeMainLanguage()
    {
        $languages = factory(Language::class, 2)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->sync($languages, ['is_active' => true, 'is_main' => false]);
        $first = $site->languages()->first();
        $first->pivot->is_main = true;
        $first->pivot->save();

        $data = $site->languages()->get()->each(function ($language) {
            $language->pivot->is_main = ! $language->pivot->is_main;
        })->map(function ($language) {
            return $language->pivot;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/languages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'pivot' => [
                            'language_code' => $languages->first()->code,
                            'is_main' => false
                        ]
                    ],
                    [
                        'pivot' => [
                            'language_code' => $languages->last()->code,
                            'is_main' => true
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->first()->code,
            'is_main' => false
        ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->last()->code,
            'is_main' => true
        ]);
    }

    public function testUpdateSiteLanguagesBySiteIdChangeNoMainLanguages()
    {
        $languages = factory(Language::class, 2)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->sync($languages, ['is_active' => false, 'is_main' => false]);

        $data = $site->languages()->get()->each(function ($language) {
            $language->pivot->is_active = ! $language->pivot->is_active;
        })->map(function ($language) {
            return $language->pivot;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/languages/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'pivot' => [
                            'language_code' => $languages->first()->code,
                            'is_main' => true
                        ]
                    ]
                ]
            ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->first()->code,
            'is_main' => true
        ]);
    }

    public function testUpdateSiteLanguageBySiteIdAndCode()
    {
        $language = factory(Language::class)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->save($language, ['is_active' => true]);

        $created = $site->languages()->first();
        $created->pivot->is_active = false;
        $data = $created->pivot->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/language/' . $language->code . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'is_active' => true,
                    'pivot' => [
                        'is_active' => true
                    ]
                ]
            ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $language->code,
            'is_active' => true
        ]);
    }

    public function testUpdateSiteLanguageBySiteIdAndCodeChangeMainLanguage()
    {
        $languages = factory(Language::class, 2)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->sync($languages, ['is_active' => true]);
        $first = $site->languages()->first();
        $first->pivot->is_main = true;
        $first->pivot->save();

        $updatedLanguage = $site->languages()->get()->last();
        $updatedLanguage->pivot->is_main = true;
        $data = $updatedLanguage->pivot->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/language/' . $updatedLanguage->code . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'pivot' => [
                        'is_main' => true
                    ]
                ]
            ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->first()->code,
            'is_main' => false
        ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->last()->code,
            'is_main' => true
        ]);
    }

    public function testUpdateSiteLanguageBySiteIdAndCodeChangeNoMainLanguages()
    {
        $languages = factory(Language::class, 2)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->sync($languages, ['is_active' => true, 'is_main' => false]);
        $first = $site->languages()->first();
        $first->pivot->is_main = true;
        $first->pivot->save();

        $first->pivot->is_main = false;
        $data = $first->pivot->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/language/' . $first->code . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'pivot' => [
                        'language_code' => $languages->first()->code,
                        'is_main' => true
                    ]
                ]
            ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->first()->code,
            'is_main' => true
        ]);
    }

    public function testDeleteSiteLanguagesBySiteId()
    {
        $languages = factory(Language::class, 3)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->sync($languages, ['is_active' => true]);

        $data = $site->languages()->get()->map(function ($language) {
            return $language->pivot;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/site/' . $site->id . '/languages', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseHas('languages', [
            'code' => $languages->first()->code
        ]);

        $this->assertDatabaseMissing('site_languages', [
            'language_code' => $languages->first()->code
        ]);
    }

    public function testDeleteSiteLanguagesBySiteIdNewMainLanguage()
    {
        $languages = factory(Language::class, 4)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->sync($languages, ['is_active' => true]);

        $last = collect($site->languages)->last();
        $last->pivot->is_main = true;
        $last->pivot->save();

        $data = $site->languages()->wherePivot('display_order', '>=', 3)->get()->map(function ($language) {
            return $language->pivot;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/site/' . $site->id . '/languages', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseHas('languages', [
            'code' => $languages->first()->code
        ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->first()->code,
            'is_main' => true
        ]);

        $this->assertDatabaseMissing('site_languages', [
            'language_code' => $languages->last()->code
        ]);
    }

    public function testDeleteSiteLanguageBySiteIdAndCode()
    {
        $languages = factory(Language::class, 2)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->sync($languages, ['is_active' => true]);

        $first = $site->languages()->first();
        $first->pivot->is_main = true;
        $first->pivot->save();

        $data = $site->languages()->first()->pivot->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/site/' . $site->id . '/language/' . $first->code, ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseHas('languages', [
            'code' => $languages->first()->code
        ]);

        $this->assertDatabaseMissing('site_languages', [
            'language_code' => $languages->first()->code
        ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->last()->code,
            'is_main' => true
        ]);
    }

    public function testReorderSiteLanguagesBySiteId()
    {
        $languages = factory(Language::class, 3)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->sync($languages, ['is_active' => true]);

        /** @var Language|Language[]|\Illuminate\Support\Collection $siteLanguages */
        $siteLanguages = $site->languages;
        $siteLanguages->first()->pivot->display_order = 3;
        $siteLanguages->last()->pivot->display_order = 1;

        $data = $siteLanguages->map(function ($language) {
            return $language->pivot;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/languages/reorder', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->first()->code,
            'display_order' => 3
        ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->last()->code,
            'display_order' => 1
        ]);
    }

    public function testReorderSiteLanguagesBySiteIdErrorWithTheSameOrder()
    {
        $languages = factory(Language::class, 3)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->sync($languages, ['is_active' => true]);

        /** @var Language|Language[]|\Illuminate\Support\Collection $siteLanguages */
        $siteLanguages = $site->languages;
        $siteLanguages->first()->pivot->display_order = 1;
        $siteLanguages->last()->pivot->display_order = 1;

        $data = $siteLanguages->map(function ($language) {
            return $language->pivot;
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/languages/reorder', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->first()->code,
            'display_order' => 1
        ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->last()->code,
            'display_order' => 3
        ]);
    }

    public function testReorderSiteLanguagesBySiteIdErrorWithMissingOrder()
    {
        $languages = factory(Language::class, 3)->create(['is_active' => true]);
        $site = factory(Site::class)->create();
        $site->languages()->sync($languages, ['is_active' => true]);

        $data = $site->languages()->get()->map(function ($language) {
            return collect($language->pivot)->except('display_order');
        })->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site/' . $site->id . '/languages/reorder', ['data' => $data], $header);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->first()->code,
            'display_order' => 1
        ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => $languages->last()->code,
            'display_order' => 3
        ]);
    }

    public function testStoreUseEnglishAsMainLanguage()
    {
        $params = [
            'domain_name' => self::$faker->unique()->domainName,
            'is_active' => true
        ];

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/site', $params, $header);

        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'result',
                'data' => [
                    'domain_name', 'is_active'
                ]
            ])
            ->assertJson([
                'result' => true,
                'data' => [
                    'domain_name' => $params['domain_name']
                ]
            ]);

        $this->assertDatabaseHas('sites', [
            'domain_name' => $params['domain_name']
        ]);

        $this->assertDatabaseHas('site_languages', [
            'language_code' => 'en',
            'is_main' => true
        ]);
    }

    public function testGetPagesBySiteId()
    {
        $site = factory(Site::class)->create();
        $pages = collect([]);
        factory(Template::class, 3)
            ->create(['site_id' => $site->id])
            ->each(function ($template) use (&$pages) {
                $items = factory(Page::class, 3)->create(['template_id' => $template->id]);
                $pages = $pages->merge($items);
            });

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/site/' . $site->id . '/pages', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'parents', 'children'
                    ]
                ]
            ])
            ->assertJson([
                'result' => true
            ]);
//            ->assertJsonFragment($pages->first()->toArray())
//            ->assertJsonFragment($pages->last()->toArray());
    }

    public function testGetSiteTranslationsBySiteId()
    {
        $site = factory(Site::class)->create();

        /** @var Language $english */
        $english = Language::firstOrCreate([
            'code' => 'en',
            'name' => 'English'
        ]);

        /** @var Language $thai */
        $thai = Language::firstOrCreate([
            'code' => 'th',
            'name' => 'Thai'
        ]);
        $site->languages()->save($english, ['is_main' => true]);
        $site->languages()->save($thai);

        $globalItem = factory(GlobalItem::class)->create(['site_id' => $site->id]);

        /** @var GlobalItemOption|GlobalItemOption[]|\Illuminate\Support\Collection $globalItemOptions */
        $globalItemOptions = factory(GlobalItemOption::class, 3)
            ->create(['global_item_id' => $globalItem->id])
            ->each(function ($item) use ($thai, $english) {
                /** @var GlobalItemOption $item */
                $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
                $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
                $item->upsertOptionSiteTranslation($english->code, 'TEST');
                $item->upsertOptionSiteTranslation($thai->code, 'เทส');
            });

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/site/' . $site->id . '/siteTranslations', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    "$english->code" => [
                        [
                            'item_id' => $globalItemOptions->first()->id,
                            'translated_text' => 'TEST'
                        ]
                    ],
                    "$thai->code" => [
                        [
                            'item_id' => $globalItemOptions->first()->id,
                            'translated_text' => 'เทส'
                        ]
                    ]
                ]
            ]);
    }

    public function testGetSiteTranslationsBySiteIdAndLanguageCode()
    {
        $site = factory(Site::class)->create();

        /** @var Language $english */
        $english = Language::firstOrCreate([
            'code' => 'en',
            'name' => 'English'
        ]);

        /** @var Language $thai */
        $thai = Language::firstOrCreate([
            'code' => 'th',
            'name' => 'Thai'
        ]);
        $site->languages()->save($english, ['is_main' => true]);
        $site->languages()->save($thai);

        $globalItem = factory(GlobalItem::class)->create(['site_id' => $site->id]);

        /** @var GlobalItemOption|GlobalItemOption[]|\Illuminate\Support\Collection $globalItemOptions */
        $globalItemOptions = factory(GlobalItemOption::class, 3)
            ->create(['global_item_id' => $globalItem->id])
            ->each(function ($item) use ($thai, $english) {
                /** @var GlobalItemOption $item */
                $item->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
                $item->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
                $item->upsertOptionSiteTranslation($english->code, 'TEST');
                $item->upsertOptionSiteTranslation($thai->code, 'เทส');
            });

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/site/' . $site->id . '/siteTranslations/' . $english->code, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    [
                        'item_id' => $globalItemOptions->first()->id,
                        'translated_text' => 'TEST'
                    ]
                ]
            ]);
    }
    
//    public function testReorderGlobalItemsBySiteId()
//    {
//        $site = factory(Site::class)->create();
//
//        /** @var GlobalItem|GlobalItem[]|\Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)->create([
//            'site_id' => $site->id
//        ])->each(function ($item, $key) {
//            /** @var GlobalItem $item */
//            $item->name = 'USED TO BE NUMBER ' . ($key + 1);
//            $item->save();
//        });
//
//        $globalItems->first()->display_order = 3;
//        $globalItems->last()->display_order = 1;
//
//        $data = $globalItems->toArray();
//
//        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);
//
//        $response = $this
//            ->actingAs(self::$developer)
//            ->post(self::$apiPrefix . '/site/' . $site->id . '/globalItems/reorder', ['data' => $data], $header);
//
//        $response
//            ->assertSuccessful()
//            ->assertJson([
//                'result' => true
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'name' => 'USED TO BE NUMBER 1',
//            'display_order' => 3
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'name' => 'USED TO BE NUMBER 3',
//            'display_order' => 1
//        ]);
//    }
//
//    public function testReorderGlobalItemsBySiteIdErrorWithTheSameOrder()
//    {
//        $site = factory(Site::class)->create();
//
//        /** @var GlobalItem|GlobalItem[]|\Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)->create([
//            'site_id' => $site->id
//        ])->each(function ($item, $key) {
//            /** @var GlobalItem $item */
//            $item->name = 'USED TO BE NUMBER ' . ($key + 1);
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
//            ->post(self::$apiPrefix . '/site/' . $site->id . '/globalItems/reorder', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'name' => 'USED TO BE NUMBER 1',
//            'display_order' => 1
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'name' => 'USED TO BE NUMBER 3',
//            'display_order' => 3
//        ]);
//    }
//
//    public function testReorderGlobalItemsBySiteIdErrorWithMissingOrder()
//    {
//        $site = factory(Site::class)->create();
//
//        /** @var GlobalItem|GlobalItem[]|\Illuminate\Support\Collection $globalItems */
//        $globalItems = factory(GlobalItem::class, 3)->create([
//            'site_id' => $site->id
//        ])->each(function ($item, $key) {
//            /** @var GlobalItem $item */
//            $item->name = 'USED TO BE NUMBER ' . ($key + 1);
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
//            ->post(self::$apiPrefix . '/site/' . $site->id . '/globalItems/reorder', ['data' => $data], $header);
//
//        $response
//            ->assertJson([
//                'result' => false
//            ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'name' => 'USED TO BE NUMBER 1',
//            'display_order' => 1
//        ]);
//
//        $this->assertDatabaseHas('global_items', [
//            'name' => 'USED TO BE NUMBER 3',
//            'display_order' => 3
//        ]);
//    }
}