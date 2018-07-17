<?php

namespace Tests\CmsApis;

use App\Api\Constants\OptionElementTypeConstants;
use App\Api\Constants\OptionValueConstants;
use App\Api\Constants\ValidationRuleConstants;
use App\Api\Models\Component;
use App\Api\Models\ComponentOption;
use App\Api\Models\GlobalItem;
use App\Api\Models\GlobalItemOption;
use App\Api\Models\Language;
use App\Api\Models\Page;
use App\Api\Models\PageItem;
use App\Api\Models\RedirectUrl;
use App\Api\Models\Site;
use App\Api\Models\Template;
use Tests\CmsApiTestCase;

class HelperTest extends CmsApiTestCase
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
     * @var Component
     */
    private $component;

    /**
     * @var GlobalItem
     */
    private $globalItem;

    /**
     * @var RedirectUrl
     */
    private $redirectUrl;

    /**
     * @var Language
     */
    private $english;

    /**
     * @var Language
     */
    private $thai;

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
        $this->thai = Language::firstOrCreate([
            'code' => 'th',
            'name' => 'Thai'
        ]);

        $this->site->languages()->save($this->english, ['is_main' => true]);
        $this->site->languages()->save($this->thai);

        $this->redirectUrl = factory(RedirectUrl::class)->create([
            'site_id' => $this->site->id,
            'is_active' => true
        ]);

        $this->component = factory(Component::class)->create();
        $componentOption = factory(ComponentOption::class)->create([
            'component_id' => $this->component->id,
            'is_active' => true
        ]);
        $componentOption->upsertOptionValue(OptionValueConstants::STRING, null);
        $componentOption->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, json_encode([
            'min' => 0,
            'max' => 255
        ]));

        $this->template = factory(Template::class)->create(['site_id' => $this->site->id]);

        $this->page = factory(Page::class)->create([
            'template_id' => $this->template->id,
            'is_active' => true
        ]);
        factory(PageItem::class)->create([
            'page_id' => $this->page->id,
            'component_id' => $this->component->id,
            'is_active' => true
        ]);


        $this->globalItem = factory(GlobalItem::class)->create(['site_id' => $this->site->id]);
        $globalItemOption = factory(GlobalItemOption::class)->create([
            'global_item_id' => $this->globalItem->id,
            'is_active' => true
        ]);
        $globalItemOption->upsertOptionValue(OptionValueConstants::STRING, 'GLOBAL TEST');
        $globalItemOption->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, json_encode([
            'min' => 0,
            'max' => 255
        ]));
    }

    public function testGetSiteData()
    {
        $response = $this->get(self::$apiPrefix . '/helpers/site/' . $this->site->domain_name, [ValidationRuleConstants::CMS_APPLICATION_NAME_HEADER => 'testing']);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'domain_name' => $this->site->domain_name,
                    'main_language' => [
                        'code' => $this->english->code,
                        'pivot' => [
                            'site_id' => $this->site->id,
                            'language_code' => $this->english->code,
                            'is_main' => true
                        ]
                    ],
                    'languages' => [
                        [
                            'code' => $this->english->code,
                        ],
                        [
                            'code' => $this->thai->code,
                        ],
                    ]
                ]
            ]);
    }

    public function testGetRedirectUrlBySourceUrl()
    {
        $response = $this->post(self::$apiPrefix . '/helpers/site/' . $this->site->domain_name . '/redirectUrl', [
            'source_url' => $this->redirectUrl->source_url
        ], [
            ValidationRuleConstants::CMS_APPLICATION_NAME_HEADER => 'testing'
        ]);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => [
                    'source_url' => $this->redirectUrl->source_url,
                    'destination_url' => $this->redirectUrl->destination_url,
                    'site_id' => $this->site->id
                ]
            ]);
    }
}
