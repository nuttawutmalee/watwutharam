<?php

namespace Tests\CmsApis;

use App\Api\Constants\LogConstants;
use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Models\CmsLog;
use App\Api\Models\Language;
use App\Api\Models\Page;
use App\Api\Models\PageItem;
use App\Api\Models\PageItemOption;
use App\Api\Models\Site;
use App\Api\Models\Template;
use Tests\CmsApiTestCase;

class CmsLogTest extends CmsApiTestCase
{
    public function testGetCmsLogs()
    {
        $logs = CmsLog::where('updated_by', '<>', LogConstants::SYSTEM)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->transform(function ($log) {
                if (preg_match('/(_BEFORE_|CREATED)/', $log->action)) {
                    $log->recoverable = true;
                } else {
                    $log->recoverable = false;
                }
                return $log;
            })
            ->all();

        $response = $this
            ->actingAs(self::$developer)
            ->get(self::$apiPrefix . '/logs', self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $logs
            ]);
    }

    public function testRecoverRecoverableItemCreatedToDelete()
    {
        $site = factory(Site::class)->create();
        $template = factory(Template::class)->create(['site_id' => $site->id]);
        $logs = CmsLog::where('action', LogConstants::SITE_CREATED)->get();

        sleep(1);

        $data = collect($logs)->pluck((new CmsLog)->getKeyName())->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/logs/recover', ['data' => $data], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => true
            ]);

        $this->assertDatabaseMissing('sites', [
            $site->getKeyName() => $site->getKey()
        ]);

        $this->assertDatabaseHas('templates', [
            $template->getKeyName() => $template->getKey()
        ]);

        //Recover delete site
        $logs = CmsLog::where('action', LogConstants::SITE_BEFORE_DELETED)->orderBy('updated_at', 'desc')->get();

        sleep(1);

        $data = collect($logs)->pluck((new CmsLog)->getKeyName())->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/logs/recover', ['data' => $data], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => true
            ]);

        $this->assertDatabaseHas('sites', [
            $site->getKeyName() => $site->getKey()
        ]);

        $this->assertDatabaseHas('templates', [
            $template->getKeyName() => $template->getKey()
        ]);
    }

    public function testRecoverRecoverableItemUpdate()
    {
        $site = factory(Site::class)->create();
        $oldDomainName = $site->domain_name;

        sleep(1);

        $newDomainName = 'test';
        $site->domain_name = $newDomainName;
        $site->save();

        $logs = CmsLog::where('action', 'like', LogConstants::SITE_BEFORE_UPDATED)->get();

        $data = collect($logs)->pluck((new CmsLog)->getKeyName())->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/logs/recover', ['data' => $data], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => true
            ]);

        $this->assertDatabaseMissing('sites', [
            $site->getKeyName() => $site->getKey(),
            'domain_name' => $newDomainName
        ]);

        $this->assertDatabaseHas('sites', [
            $site->getKeyName() => $site->getKey(),
            'domain_name' => $oldDomainName
        ]);
    }

    public function testRecoverComplexStructureCreateToDelete()
    {
        $site = factory(Site::class)->create(['is_active' => true]);

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
        $option = factory(PageItemOption::class)->create(['page_item_id' => $pageItem->id]);
        $option->upsertOptionValue(OptionValueConstants::STRING, 'TEST');
        $option->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX);
        $option->upsertOptionSiteTranslation($english->code, 'TEST');
        $option->upsertOptionSiteTranslation($thai->code, 'เทส');

        $option->withOptionSiteTranslation($thai->code);
        $option['translated_text'] = 'UPDATE เทส';
        $option['language_code'] = $thai->code;
        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption/' . $option->id . '/update', ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'site_translations' => [
                        [
                            'language_code' => $english->code
                        ],
                        [
                            'language_code' => $thai->code
                        ]
                    ]
                ]
            ])
            ->assertJsonFragment([
                'language_code' => $thai->code,
                'translated_text' => 'UPDATE เทส'
            ])
            ->assertJsonFragment([
                'language_code' => $english->code,
                'translated_text' => 'TEST'
            ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseHas('site_translations', [
            'language_code' => $thai->code,
            'translated_text' => 'UPDATE เทส'
        ]);

        $logs = CmsLog::where(function ($query) {
            /** @var \Illuminate\Database\Eloquent\Builder $query */
            $query->where('action', 'like', LogConstants::PAGE_CREATED)
               ->orWhere('action', 'like', LogConstants::PAGE_ITEM_CREATED)
               ->orWhere('action', 'like', LogConstants::PAGE_ITEM_OPTION_CREATED)
               ->orWhere('action', 'like', LogConstants::PAGE_ITEM_OPTION_STRING_CREATED)
               ->orWhere('action', 'like', LogConstants::SITE_TRANSLATION_CREATED)
               ->orWhere('action', 'like', LogConstants::SITE_TRANSLATION_BEFORE_UPDATED);
        })->get();

        $data = collect($logs)->pluck((new CmsLog)->getKeyName())->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/logs/recover', ['data' => $data], self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => true
            ]);

        $this->assertDatabaseMissing('pages', [
            $page->getKeyName() => $page->getKey()
        ]);

        $this->assertDatabaseMissing('page_items', [
            $pageItem->getKeyName() => $pageItem->getKey()
        ]);

        $this->assertDatabaseMissing('page_item_options', [
            $option->getKeyName() => $option->getKey()
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $english->code,
            'translated_text' => 'TEST'
        ]);

        $this->assertDatabaseMissing('site_translations', [
            'language_code' => $thai->code,
            'translated_text' => 'UPDATE เทส'
        ]);
    }
}

