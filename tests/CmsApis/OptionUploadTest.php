<?php

namespace Tests\CmsApis;

use App\Api\Constants\HelperConstants;
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
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Tests\CmsApiTestCase;

class OptionUploadTest extends CmsApiTestCase
{
    /**
     * @var
     */
    private $file1;

    /**
     * @var
     */
    private $file2;

    /**
     * @var string
     */
    private $cmsPath;

    /**
     * @var Site
     */
    private $site;

    /**
     * @var Component
     */
    private $component;

    /**
     * @var Template
     */
    private $template;

    /**
     * @var TemplateItem
     */
    private $templateItem;

    /**
     * @var Page
     */
    private $page;

    /**
     * @var PageItem
     */
    private $pageItem;

    /**
     * @var GlobalItem
     */
    private $globalItem;

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

        $this->cmsPath = public_path(HelperConstants::UPLOADS_FOLDER_TESTING);

        (new Filesystem)->cleanDirectory($this->cmsPath);

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

        $this->component = factory(Component::class)->create();
        $this->template = factory(Template::class)->create(['site_id' => $this->site->id]);
        $this->templateItem = factory(TemplateItem::class)->create(['template_id' => $this->template->id]);
        $this->page = factory(Page::class)->create(['template_id' => $this->template->id]);
        $this->pageItem = factory(PageItem::class)->create(['page_id' => $this->page->id]);
        $this->globalItem = factory(GlobalItem::class)->create(['site_id' => $this->site->id]);

        $this->file1 = new \ArrayObject([
            'tempPath' => null,
            'path' => null,
            'file' => null
        ]);

        $stub = __DIR__ . '/_files/source-test.jpg';
        $name = 'test.jpg';
        $this->file1->tempPath =  base_path('tests/CmsApis/temp/' . $name);

        copy($stub, $this->file1->tempPath);

        $this->file1->file = new UploadedFile(
            $this->file1->tempPath,
            $name,
            filesize($this->file1->tempPath),
            'image/jpeg',
            null,
            true
        );

        $this->file1->path = HelperConstants::UPLOADS_FOLDER_TESTING . config('cms.' . get_cms_application() . '.uploads_path') . '/a/' . pathinfo($this->file1->file, PATHINFO_BASENAME);

        $this->file2 = new \ArrayObject([
            'tempPath' => '',
            'path' => '',
            'file' => null
        ]);

        $stub = __DIR__ . '/_files/source-test-2.jpg';
        $name = 'test-2.jpg';
        $this->file2->tempPath =  base_path('tests/CmsApis/temp/' . $name);

        copy($stub, $this->file2->tempPath);

        $this->file2->file = new UploadedFile(
            $this->file2->tempPath,
            $name,
            filesize($this->file2->tempPath),
            'image/jpeg',
            null,
            true
        );

        $this->file2->path = HelperConstants::UPLOADS_FOLDER_TESTING . config('cms.' . get_cms_application() . '.uploads_path') . '/a/' . pathinfo($this->file2->file, PATHINFO_BASENAME);
    }

    protected function tearDown()
    {
        parent::tearDown();

        @unlink($this->file1->tempPath);
        @unlink($this->file2->tempPath);

        (new Filesystem)->cleanDirectory($this->cmsPath);
    }

    /**
     * Component Option
     */

    //Create
    //File + Valid Path
    public function testUploadComponentOptionWithFileAndValidPath()
    {
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => $this->file1->path,
            'component_id' => $this->component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
//            ->assertJsonFragment([
//                'translated_text' => $this->file1->path
//            ])
            ->assertJsonFragment([
                'component_id' => $this->component->id,
                'option_value' => $this->file1->path
            ]);

        $this->assertDatabaseHas('component_options', [
            'component_id' => $this->component->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $this->file1->path
        ]);

        $this->assertFileExists(public_path() . '/' . $this->file1->path);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $this->file1->path);
    }

    //File + No Path
    public function testUploadComponentOptionWithFileAndNoPath()
    {
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'component_id' => $this->component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('component_options', [
            'component_id' => $this->component->id,
            'name' => $params['name']
        ]);
    }

    //File + Empty Path
    public function testUploadComponentOptionWithFileAndEmptyPath()
    {
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => '',
            'component_id' => $this->component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('component_options', [
            'component_id' => $this->component->id,
            'name' => $params['name']
        ]);
    }

    //File + Incorrect Path
    public function testUploadComponentOptionWithFileAndIncorrectPath()
    {
        $params = [
            'name' => 'MOCK-UP COMPONENT OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => config('cms.' . get_cms_application() . '.path') . '/a/a/a/.',
            'component_id' => $this->component->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/componentOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('component_options', [
            'component_id' => $this->component->id,
            'name' => $params['name']
        ]);
    }

    //Update
    //New File + Old Path
    public function testUpdateUploadComponentOptionWithNewFileAndOldPath()
    {
        $componentOption = factory(ComponentOption::class)->create(['component_id' => $this->component->id]);
        $savedPath = $componentOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);
        $assertPath = $this->setNumberInFilename($savedPath, 1);

        $data = $componentOption->withNecessaryData()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/componentOption/' . $componentOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'component_id' => $this->component->id,
                'option_value' => $assertPath
            ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $assertPath
        ]);

        $this->assertFileExists(public_path() . '/' . $assertPath);
        $this->assertFileEquals(realpath($this->file2->file), public_path() . '/' . $assertPath);
    }

    //New File + New Path
    public function testUpdateUploadComponentOptionWithNewFileAndNewPath()
    {
        $componentOption = factory(ComponentOption::class)->create(['component_id' => $this->component->id]);
        $savedPath = $componentOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $componentOption->withNecessaryData()->toArray();
        $data['option_value'] = $newPath;

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/componentOption/' . $componentOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'component_id' => $this->component->id,
                'option_value' => $newPath
            ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileExists(public_path() . '/' . $newPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file2->file), public_path() . '/' . $newPath);
    }

    //New File + No Path
    public function testUpdateUploadComponentOptionWithNewFileAndNoPath()
    {
        $componentOption = factory(ComponentOption::class)->create(['component_id' => $this->component->id]);
        $savedPath = $componentOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $componentOption->withNecessaryData()->except('option_value')->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/componentOption/' . $componentOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //New File + Empty Path
    public function testUpdateUploadComponentOptionWithNewFileAndEmptyPath()
    {
        $componentOption = factory(ComponentOption::class)->create(['component_id' => $this->component->id]);
        $savedPath = $componentOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $componentOption->withNecessaryData()->toArray();
        $data['option_value'] = '';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/componentOption/' . $componentOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //New File + Incorrect Path
    public function testUpdateUploadComponentOptionWithNewFileAndIncorrectPath()
    {
        $componentOption = factory(ComponentOption::class)->create(['component_id' => $this->component->id]);
        $savedPath = $componentOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $componentOption->withNecessaryData()->toArray();
        $data['option_value'] = config('cms.' . get_cms_application() . '.path') . '/a/a/a/.';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/componentOption/' . $componentOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Old Path
    public function testUpdateUploadComponentOptionWithOldFileAndOldPath()
    {
        $componentOption = factory(ComponentOption::class)->create(['component_id' => $this->component->id]);
        $savedPath = $componentOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $data = $componentOption->withNecessaryData()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/componentOption/' . $componentOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'component_id' => $this->component->id,
                'option_value' => $savedPath
            ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + New Path
    public function testUpdateUploadComponentOptionWithOldFileAndNewPath()
    {
        $componentOption = factory(ComponentOption::class)->create(['component_id' => $this->component->id]);
        $savedPath = $componentOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $componentOption->withNecessaryData()->toArray();
        $data['option_value'] = $newPath;

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/componentOption/' . $componentOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'component_id' => $this->component->id,
                'option_value' => $newPath
            ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileExists(public_path() . '/' . $newPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $newPath);
    }

    //Old File + No Path
    public function testUpdateUploadComponentOptionWithOldFileAndNoPath()
    {
        $componentOption = factory(ComponentOption::class)->create(['component_id' => $this->component->id]);
        $savedPath = $componentOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $componentOption->withNecessaryData()->except('option_value')->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/componentOption/' . $componentOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Empty Path
    public function testUpdateUploadComponentOptionWithOldFileAndEmptyPath()
    {
        $componentOption = factory(ComponentOption::class)->create(['component_id' => $this->component->id]);
        $savedPath = $componentOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $componentOption->withNecessaryData()->toArray();
        $data['option_value'] = '';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/componentOption/' . $componentOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Incorrect Path
    public function testUpdateUploadComponentOptionWithOldFileAndIncorrectPath()
    {
        $componentOption = factory(ComponentOption::class)->create(['component_id' => $this->component->id]);
        $savedPath = $componentOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $componentOption->withNecessaryData()->toArray();
        $data['option_value'] = config('cms.' . get_cms_application() . '.path') . '/a/a/a/.';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/componentOption/' . $componentOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('component_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('component_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Delete
    public function testDeleteUploadComponentOptionsNoCascade()
    {
        $fileNames = [];

        /** @var ComponentOption[]|\Illuminate\Support\Collection $options */
        $options = factory(ComponentOption::class, 3)->create([
            'component_id' => $this->component->id
        ])->each(function ($item) use (&$fileNames) {
            /** @var ComponentOption  $item */
            $savedPath = $item->upsertOptionUploadFile($this->file1->file, $this->file1->path);
            array_push($fileNames, $savedPath);
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

        $this->assertDatabaseMissing('component_options', [
            'id' => $options->first()->id
        ]);

        if ( ! empty($fileNames)) {
            collect($fileNames)->each(function ($path) {
                $this->assertFileExists(public_path() . '/' . $path);
                $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $path);
            });
        }
    }

    public function testDeleteUploadComponentOptionByIdNoCascade()
    {
        $option = factory(ComponentOption::class)->create(['component_id' => $this->component->id]);
        $savedPath = $option->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/componentOption/' . $option->id, ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('component_options', [
            'id' => $option->id
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    /**
     * Template Item Option
     */

    //Create
    //File + Valid Path
    public function testUploadTemplateItemOptionWithFileAndValidPath()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => $this->file1->path,
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
//            ->assertJsonFragment([
//                'translated_text' => $this->file1->path
//            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_value' => $this->file1->path
            ]);

        $this->assertDatabaseHas('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $this->file1->path
        ]);

        $this->assertFileExists(public_path() . '/' . $this->file1->path);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $this->file1->path);
    }

    //File + No Path
    public function testUploadTemplateItemOptionWithFileAndNoPath()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);
    }

    //File + Empty Path
    public function testUploadTemplateItemOptionWithFileAndEmptyPath()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => '',
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);
    }

    //File + Incorrect Path
    public function testUploadTemplateItemOptionWithFileAndIncorrectPath()
    {
        $params = [
            'name' => 'MOCK-UP TEMPLATE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => config('cms.' . get_cms_application() . '.path') . '/a/a/a/.',
            'template_item_id' => $this->templateItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/templateItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('template_item_options', [
            'template_item_id' => $this->templateItem->id,
            'name' => $params['name']
        ]);
    }

    //Update
    //New File + Old Path
    public function testUpdateUploadTemplateItemOptionWithNewFileAndOldPath()
    {
        $templateItemOption = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $savedPath = $templateItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);
        $assertPath = $this->setNumberInFilename($savedPath, 1);

        $data = $templateItemOption->withNecessaryData()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/templateItemOption/' . $templateItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_value' => $assertPath
            ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $assertPath
        ]);

        $this->assertFileExists(public_path() . '/' . $assertPath);
        $this->assertFileEquals(realpath($this->file2->file), public_path() . '/' . $assertPath);
    }

    //New File + New Path
    public function testUpdateUploadTemplateItemOptionWithNewFileAndNewPath()
    {
        $templateItemOption = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $savedPath = $templateItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $templateItemOption->withNecessaryData()->toArray();
        $data['option_value'] = $newPath;

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/templateItemOption/' . $templateItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_value' => $newPath
            ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileExists(public_path() . '/' . $newPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file2->file), public_path() . '/' . $newPath);
    }

    //New File + No Path
    public function testUpdateUploadTemplateItemOptionWithNewFileAndNoPath()
    {
        $templateItemOption = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $savedPath = $templateItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $templateItemOption->withNecessaryData()->except('option_value')->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/templateItemOption/' . $templateItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //New File + Empty Path
    public function testUpdateUploadTemplateItemOptionWithNewFileAndEmptyPath()
    {
        $templateItemOption = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $savedPath = $templateItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $templateItemOption->withNecessaryData()->toArray();
        $data['option_value'] = '';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/templateItemOption/' . $templateItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //New File + Incorrect Path
    public function testUpdateUploadTemplateItemOptionWithNewFileAndIncorrectPath()
    {
        $templateItemOption = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $savedPath = $templateItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $templateItemOption->withNecessaryData()->toArray();
        $data['option_value'] = config('cms.' . get_cms_application() . '.path') . '/a/a/a/.';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/templateItemOption/' . $templateItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Old Path
    public function testUpdateUploadTemplateItemOptionWithOldFileAndOldPath()
    {
        $templateItemOption = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $savedPath = $templateItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $data = $templateItemOption->withNecessaryData()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/templateItemOption/' . $templateItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_value' => $savedPath
            ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + New Path
    public function testUpdateUploadTemplateItemOptionWithOldFileAndNewPath()
    {
        $templateItemOption = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $savedPath = $templateItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $templateItemOption->withNecessaryData()->toArray();
        $data['option_value'] = $newPath;

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/templateItemOption/' . $templateItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'template_item_id' => $this->templateItem->id,
                'option_value' => $newPath
            ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileExists(public_path() . '/' . $newPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $newPath);
    }

    //Old File + No Path
    public function testUpdateUploadTemplateItemOptionWithOldFileAndNoPath()
    {
        $templateItemOption = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $savedPath = $templateItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $templateItemOption->withNecessaryData()->except('option_value')->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/templateItemOption/' . $templateItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Empty Path
    public function testUpdateUploadTemplateItemOptionWithOldFileAndEmptyPath()
    {
        $templateItemOption = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $savedPath = $templateItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $templateItemOption->withNecessaryData()->toArray();
        $data['option_value'] = '';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/templateItemOption/' . $templateItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Incorrect Path
    public function testUpdateUploadTemplateItemOptionWithOldFileAndIncorrectPath()
    {
        $templateItemOption = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $savedPath = $templateItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $templateItemOption->withNecessaryData()->toArray();
        $data['option_value'] = config('cms.' . get_cms_application() . '.path') . '/a/a/a/.';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/templateItemOption/' . $templateItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('template_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('template_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Delete
    public function testDeleteUploadTemplateItemOptionsNoCascade()
    {
        $fileNames = [];

        /** @var TemplateItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(TemplateItemOption::class, 3)->create([
            'template_item_id' => $this->templateItem->id
        ])->each(function ($item) use (&$fileNames) {
            /** @var TemplateItemOption $item */
            $savedPath = $item->upsertOptionUploadFile($this->file1->file, $this->file1->path);
            array_push($fileNames, $savedPath);
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

        $this->assertDatabaseMissing('template_item_options', [
            'id' => $options->first()->id
        ]);

        if ( ! empty($fileNames)) {
            collect($fileNames)->each(function ($path) {
                $this->assertFileExists(public_path() . '/' . $path);
                $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $path);
            });
        }
    }

    public function testDeleteUploadTemplateItemOptionByIdNoCascade()
    {
        $option = factory(TemplateItemOption::class)->create(['template_item_id' => $this->templateItem->id]);
        $savedPath = $option->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/templateItemOption/' . $option->id, ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('template_item_options', [
            'id' => $option->id
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    /**
     * Page Item Option
     */

    //Create
    //File + Valid Path
    public function testUploadPageItemOptionWithFileAndValidPath()
    {
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => $this->file1->path,
            'page_item_id' => $this->pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
//            ->assertJsonFragment([
//                'translated_text' => $this->file1->path
//            ])
            ->assertJsonFragment([
                'page_item_id' => $this->pageItem->id,
                'option_value' => $this->file1->path
            ]);

        $this->assertDatabaseHas('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $this->file1->path
        ]);

        $this->assertFileExists(public_path() . '/' . $this->file1->path);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $this->file1->path);
    }

    //File + No Path
    public function testUploadPageItemOptionWithFileAndNoPath()
    {
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'page_item_id' => $this->pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);
    }

    //File + Empty Path
    public function testUploadPageItemOptionWithFileAndEmptyPath()
    {
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => '',
            'page_item_id' => $this->pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);
    }

    //File + Incorrect Path
    public function testUploadPageItemOptionWithFileAndIncorrectPath()
    {
        $params = [
            'name' => 'MOCK-UP PAGE ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => config('cms.' . get_cms_application() . '.path') . '/a/a/a/.',
            'page_item_id' => $this->pageItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/pageItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('page_item_options', [
            'page_item_id' => $this->pageItem->id,
            'name' => $params['name']
        ]);
    }

    //Update
    //New File + Old Path
    public function testUpdateUploadPageItemOptionWithNewFileAndOldPath()
    {
        $pageItemOption = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $savedPath = $pageItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);
        $assertPath = $this->setNumberInFilename($savedPath, 1);

        $data = $pageItemOption->withNecessaryData()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/pageItemOption/' . $pageItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'page_item_id' => $this->pageItem->id,
                'option_value' => $assertPath
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $assertPath
        ]);

        $this->assertFileExists(public_path() . '/' . $assertPath);
        $this->assertFileEquals(realpath($this->file2->file), public_path() . '/' . $assertPath);
    }

    //New File + New Path
    public function testUpdateUploadPageItemOptionWithNewFileAndNewPath()
    {
        $pageItemOption = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $savedPath = $pageItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $pageItemOption->withNecessaryData()->toArray();
        $data['option_value'] = $newPath;

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/pageItemOption/' . $pageItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'page_item_id' => $this->pageItem->id,
                'option_value' => $newPath
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileExists(public_path() . '/' . $newPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file2->file), public_path() . '/' . $newPath);
    }

    //New File + No Path
    public function testUpdateUploadPageItemOptionWithNewFileAndNoPath()
    {
        $pageItemOption = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $savedPath = $pageItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $pageItemOption->withNecessaryData()->except('option_value')->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/pageItemOption/' . $pageItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //New File + Empty Path
    public function testUpdateUploadPageItemOptionWithNewFileAndEmptyPath()
    {
        $pageItemOption = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $savedPath = $pageItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $pageItemOption->withNecessaryData()->toArray();
        $data['option_value'] = '';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/pageItemOption/' . $pageItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //New File + Incorrect Path
    public function testUpdateUploadPageItemOptionWithNewFileAndIncorrectPath()
    {
        $pageItemOption = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $savedPath = $pageItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $pageItemOption->withNecessaryData()->toArray();
        $data['option_value'] = config('cms.' . get_cms_application() . '.path') . '/a/a/a/.';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/pageItemOption/' . $pageItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Old Path
    public function testUpdateUploadPageItemOptionWithOldFileAndOldPath()
    {
        $pageItemOption = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $savedPath = $pageItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $data = $pageItemOption->withNecessaryData()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/pageItemOption/' . $pageItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'page_item_id' => $this->pageItem->id,
                'option_value' => $savedPath
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + New Path
    public function testUpdateUploadPageItemOptionWithOldFileAndNewPath()
    {
        $pageItemOption = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $savedPath = $pageItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $pageItemOption->withNecessaryData()->toArray();
        $data['option_value'] = $newPath;

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/pageItemOption/' . $pageItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'page_item_id' => $this->pageItem->id,
                'option_value' => $newPath
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileExists(public_path() . '/' . $newPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $newPath);
    }

    //Old File + No Path
    public function testUpdateUploadPageItemOptionWithOldFileAndNoPath()
    {
        $pageItemOption = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $savedPath = $pageItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $pageItemOption->withNecessaryData()->except('option_value')->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/pageItemOption/' . $pageItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Empty Path
    public function testUpdateUploadPageItemOptionWithOldFileAndEmptyPath()
    {
        $pageItemOption = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $savedPath = $pageItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $pageItemOption->withNecessaryData()->toArray();
        $data['option_value'] = '';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/pageItemOption/' . $pageItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Incorrect Path
    public function testUpdateUploadPageItemOptionWithOldFileAndIncorrectPath()
    {
        $pageItemOption = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $savedPath = $pageItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $pageItemOption->withNecessaryData()->toArray();
        $data['option_value'] = config('cms.' . get_cms_application() . '.path') . '/a/a/a/.';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/pageItemOption/' . $pageItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('page_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('page_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Delete
    public function testDeleteUploadPageItemOptionsNoCascade()
    {
        $fileNames = [];

        /** @var PageItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(PageItemOption::class, 3)->create([
            'page_item_id' => $this->pageItem->id
        ])->each(function ($item) use (&$fileNames) {
            /** @var PageItemOption $item */
            $savedPath = $item->upsertOptionUploadFile($this->file1->file, $this->file1->path);
            array_push($fileNames, $savedPath);
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

        $this->assertDatabaseMissing('page_item_options', [
            'id' => $options->first()->id
        ]);

        if ( ! empty($fileNames)) {
            collect($fileNames)->each(function ($path) {
                $this->assertFileExists(public_path() . '/' . $path);
                $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $path);
            });
        }
    }

    public function testDeleteUploadPageItemOptionByIdNoCascade()
    {
        $option = factory(PageItemOption::class)->create(['page_item_id' => $this->pageItem->id]);
        $savedPath = $option->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/pageItemOption/' . $option->id, ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('page_item_options', [
            'id' => $option->id
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    /**
     * Global Item Option
     */

    //Create
    //File + Valid Path
    public function testUploadGlobalItemOptionWithFileAndValidPath()
    {
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => $this->file1->path,
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true
            ])
//            ->assertJsonFragment([
//                'translated_text' => $this->file1->path
//            ])
            ->assertJsonFragment([
                'global_item_id' => $this->globalItem->id,
                'option_value' => $this->file1->path
            ]);

        $this->assertDatabaseHas('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $this->file1->path
        ]);

        $this->assertFileExists(public_path() . '/' . $this->file1->path);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $this->file1->path);
    }

    //File + No Path
    public function testUploadGlobalItemOptionWithFileAndNoPath()
    {
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);
    }

    //File + Empty Path
    public function testUploadGlobalItemOptionWithFileAndEmptyPath()
    {
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => '',
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);
    }

    //File + Incorrect Path
    public function testUploadGlobalItemOptionWithFileAndIncorrectPath()
    {
        $params = [
            'name' => 'MOCK-UP GLOBAL ITEM OPTION',
            'variable_name' => self::randomVariableName(),
            'option_type' => OptionValueConstants::STRING,
            'option_value' => config('cms.' . get_cms_application() . '.path') . '/a/a/a/.',
            'global_item_id' => $this->globalItem->id,
            'element_type' => OptionElementTypeConstants::TEXTBOX,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/globalItemOption', $params, self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseMissing('global_item_options', [
            'global_item_id' => $this->globalItem->id,
            'name' => $params['name']
        ]);
    }

    //Update
    //New File + Old Path
    public function testUpdateUploadGlobalItemOptionWithNewFileAndOldPath()
    {
        $globalItemOption = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $savedPath = $globalItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);
        $assertPath = $this->setNumberInFilename($savedPath, 1);

        $data = $globalItemOption->withNecessaryData()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/globalItemOption/' . $globalItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'global_item_id' => $this->globalItem->id,
                'option_value' => $assertPath
            ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $assertPath
        ]);

        $this->assertFileExists(public_path() . '/' . $assertPath);
        $this->assertFileEquals(realpath($this->file2->file), public_path() . '/' . $assertPath);
    }

    //New File + New Path
    public function testUpdateUploadGlobalItemOptionWithNewFileAndNewPath()
    {
        $globalItemOption = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $savedPath = $globalItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $globalItemOption->withNecessaryData()->toArray();
        $data['option_value'] = $newPath;

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/globalItemOption/' . $globalItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'global_item_id' => $this->globalItem->id,
                'option_value' => $newPath
            ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileExists(public_path() . '/' . $newPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file2->file), public_path() . '/' . $newPath);
    }

    //New File + No Path
    public function testUpdateUploadGlobalItemOptionWithNewFileAndNoPath()
    {
        $globalItemOption = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $savedPath = $globalItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $globalItemOption->withNecessaryData()->except('option_value')->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/globalItemOption/' . $globalItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //New File + Empty Path
    public function testUpdateUploadGlobalItemOptionWithNewFileAndEmptyPath()
    {
        $globalItemOption = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $savedPath = $globalItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $globalItemOption->withNecessaryData()->toArray();
        $data['option_value'] = '';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/globalItemOption/' . $globalItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //New File + Incorrect Path
    public function testUpdateUploadGlobalItemOptionWithNewFileAndIncorrectPath()
    {
        $globalItemOption = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $savedPath = $globalItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $globalItemOption->withNecessaryData()->toArray();
        $data['option_value'] = config('cms.' . get_cms_application() . '.path') . '/a/a/a/.';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/globalItemOption/' . $globalItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file2->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Old Path
    public function testUpdateUploadGlobalItemOptionWithOldFileAndOldPath()
    {
        $globalItemOption = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $savedPath = $globalItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $data = $globalItemOption->withNecessaryData()->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/globalItemOption/' . $globalItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'global_item_id' => $this->globalItem->id,
                'option_value' => $savedPath
            ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + New Path
    public function testUpdateUploadGlobalItemOptionWithOldFileAndNewPath()
    {
        $globalItemOption = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $savedPath = $globalItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $globalItemOption->withNecessaryData()->toArray();
        $data['option_value'] = $newPath;

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/globalItemOption/' . $globalItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
            ])
            ->assertJsonFragment([
                'global_item_id' => $this->globalItem->id,
                'option_value' => $newPath
            ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileExists(public_path() . '/' . $newPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $newPath);
    }

    //Old File + No Path
    public function testUpdateUploadGlobalItemOptionWithOldFileAndNoPath()
    {
        $globalItemOption = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $savedPath = $globalItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $globalItemOption->withNecessaryData()->except('option_value')->toArray();

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/globalItemOption/' . $globalItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Empty Path
    public function testUpdateUploadGlobalItemOptionWithOldFileAndEmptyPath()
    {
        $globalItemOption = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $savedPath = $globalItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $globalItemOption->withNecessaryData()->toArray();
        $data['option_value'] = '';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/globalItemOption/' . $globalItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Old File + Incorrect Path
    public function testUpdateUploadGlobalItemOptionWithOldFileAndIncorrectPath()
    {
        $globalItemOption = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $savedPath = $globalItemOption->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $newPath = preg_replace('/\/a\//i', '/b/', $savedPath);
        $newPath = preg_replace('/' . pathinfo($this->file1->file, PATHINFO_FILENAME) . '.' . pathinfo($this->file1->file, PATHINFO_EXTENSION) . '/i', 'test-new.' . pathinfo($this->file1->file, PATHINFO_EXTENSION), $newPath);

        $data = $globalItemOption->withNecessaryData()->toArray();
        $data['option_value'] = config('cms.' . get_cms_application() . '.path') . '/a/a/a/.';

        $response = $this
            ->actingAs(self::$developer)
            ->post(
                self::$apiPrefix . '/globalItemOption/' . $globalItemOption->id . '/update',
                [
                    'data' => $data,
                    'file' => $this->file1->file
                ],
                self::$developerAuthorizationHeader);

        $response
            ->assertJson([
                'result' => false
            ]);

        $this->assertDatabaseHas('global_item_option_strings', [
            'option_value' => $savedPath
        ]);

        $this->assertDatabaseMissing('global_item_option_strings', [
            'option_value' => $newPath
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    //Delete
    public function testDeleteUploadGlobalItemOptionsNoCascade()
    {
        $fileNames = [];

        /** @var GlobalItemOption[]|\Illuminate\Support\Collection $options */
        $options = factory(GlobalItemOption::class, 3)->create([
            'global_item_id' => $this->globalItem->id
        ])->each(function ($item) use (&$fileNames) {
            /** @var GlobalItemOption $item */
            $savedPath = $item->upsertOptionUploadFile($this->file1->file, $this->file1->path);
            array_push($fileNames, $savedPath);
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

        $this->assertDatabaseMissing('global_item_options', [
            'id' => $options->first()->id
        ]);

        if ( ! empty($fileNames)) {
            collect($fileNames)->each(function ($path) {
                $this->assertFileExists(public_path() . '/' . $path);
                $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $path);
            });
        }
    }

    public function testDeleteUploadGlobalItemOptionByIdNoCascade()
    {
        $option = factory(GlobalItemOption::class)->create(['global_item_id' => $this->globalItem->id]);
        $savedPath = $option->upsertOptionUploadFile($this->file1->file, $this->file1->path);

        $data = $option->toArray();

        $header = self::getURLEncodedHeader(self::$developerAuthorizationHeader);

        $response = $this
            ->actingAs(self::$developer)
            ->delete(self::$apiPrefix . '/globalItemOption/' . $option->id, ['data' => $data], $header);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => null
            ]);

        $this->assertDatabaseMissing('global_item_options', [
            'id' => $option->id
        ]);

        $this->assertFileExists(public_path() . '/' . $savedPath);
        $this->assertFileEquals(realpath($this->file1->file), public_path() . '/' . $savedPath);
    }

    /**
     * Customs
     */
    public function testQuickUpload()
    {
        $params = [
            'path' => $this->file1->path,
            'file' => $this->file1->file
        ];

        $response = $this
            ->actingAs(self::$developer)
            ->post(self::$apiPrefix . '/quick-upload', $params, self::$developerAuthorizationHeader);

        $response
            ->assertSuccessful()
            ->assertJson([
                'result' => true,
                'data' => $this->file1->path
            ]);
    }
}