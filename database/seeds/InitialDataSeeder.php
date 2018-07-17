<?php

use Illuminate\Database\Seeder;
use App\Api\Models\CmsRole;
use App\Api\Models\User;
use App\Api\Models\Language;
use App\Api\Models\Component;
use App\Api\Constants\OptionValueConstants;
use App\Api\Constants\RoleConstants;
use App\Api\Constants\OptionElementTypeConstants;
use Illuminate\Support\Facades\DB;

class InitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } catch (\Exception $exception) {};

        $this->generateUserAndRoles();

        $this->generateLanguage();

        $this->generateComponents();

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Exception $exception) {};
    }

    /**
     * Generate User and Roles
     */
    private function generateUserAndRoles()
    {
        CmsRole::truncate();

        /** @var CmsRole $developer_role */
        $developer_role = CmsRole::create([
            "name" => RoleConstants::DEVELOPER,
            "is_developer" => true,
            "allow_structure" => true,
            "allow_content" => true,
            "allow_user" => true,
            "updated_by" => "SYSTEM"
        ]);

        /** @var CmsRole $admin_role */
        $admin_role = CmsRole::create([
            "name" => RoleConstants::ADMINISTRATOR,
            "is_developer" => false,
            "allow_structure" => true,
            "allow_content" => true,
            "allow_user" => true,
            "updated_by" => "SYSTEM"
        ]);

        CmsRole::create([
            "name" => RoleConstants::EDITORIAL,
            "is_developer" => false,
            "allow_structure" => false,
            "allow_content" => true,
            "allow_user" => false,
            "updated_by" => "SYSTEM"
        ]);

        User::truncate();

        User::create([
            "name" => "QUO Developer",
            "email" => "developers@cms.com",
            "password" => 'developers',
            'role_id' => $developer_role->id
        ]);

        User::create([
            "name" => "Administrator",
            "email" => "admin@cms.com",
            "password" => 'admin',
            'role_id' => $admin_role->id
        ]);
    }

    /**
     * Generate Language
     */
    private function generateLanguage()
    {
        Language::firstOrCreate([
            'code' => config('cms.' . get_cms_application() . '.main_language.code'),
            'name' => config('cms.' . get_cms_application() . '.main_language.name'),
            'locale' => config('cms.' . get_cms_application() . '.main_language.locale'),
            'hreflang' => config('cms.' . get_cms_application() . '.main_language.hreflang')
        ]);
    }

    /**
     * Generate Necessary Components
     */
    private function generateComponents()
    {
        Component::truncate();

        // Checkbox
        /** @var Component $checkbox */
        $checkbox = Component::create([
            'name' => 'Checkbox',
            'variable_name' => 'checkbox'
        ]);
        /** @var \App\Api\Models\ComponentOption $checkboxValue */
        $checkboxValue = $checkbox->componentOptions()->create([
            'name' => 'Value',
            'variable_name' => 'value',
            'is_required' => true,
            'is_active' => true
        ]);
        $checkboxValue->upsertOptionValue(OptionValueConstants::INTEGER, 0);
        $checkboxValue->upsertOptionElementType(OptionElementTypeConstants::CHECKBOX, [
            'checkedValue' => '',
            'checkedText' => 'Yes',
            'uncheckedText' => 'No'
        ]);


        // Menu
        /** @var Component $menu */
        $menu = Component::create([
            'name' => 'Menu',
            'variable_name' => 'menu'
        ]);
        /** @var \App\Api\Models\ComponentOption $menuUrl */
        $menuUrl = $menu->componentOptions()->create([
            'name' => 'Url',
            'variable_name' => 'url',
            'is_required' => true,
            'is_active' => true
        ]);
        $menuUrl->upsertOptionValue(OptionValueConstants::STRING, '#');
        $menuUrl->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => 'url'
        ]);

        /** @var \App\Api\Models\ComponentOption $menuTarget */
        $menuTarget = $menu->componentOptions()->create([
            'name' => 'Target',
            'variable_name' => 'target',
            'is_required' => true,
            'is_active' => true
        ]);
        $menuTarget->upsertOptionValue(OptionValueConstants::STRING, '_self');
        $menuTarget->upsertOptionElementType(OptionElementTypeConstants::DROPDOWN, [
            [
                'text' => 'Same page',
                'value' => '_self'
            ],
            [
                'text' => 'New page',
                'value' => '_blank'
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $menuLabel */
        $menuLabel = $menu->componentOptions()->create([
            'name' => 'Label',
            'variable_name' => 'label',
            'is_required' => false,
            'is_active' => true
        ]);
        $menuLabel->upsertOptionValue(OptionValueConstants::STRING, '#');
        $menuLabel->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $menuImage */
        $menuImage = $menu->componentOptions()->create([
            'name' => 'Image',
            'variable_name' => 'image',
            'is_required' => false,
            'is_active' => true
        ]);
        $menuImage->upsertOptionValue(OptionValueConstants::STRING, '');
        $menuImage->upsertOptionElementType(OptionElementTypeConstants::FILE_UPLOAD, [
            'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'imageSizeOption' => [
                'width' => '',
                'height' => '',
                'ratio' => '',
                'size' => ''
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $menuImageAlt */
        $menuImageAlt = $menu->componentOptions()->create([
            'name' => 'Image Alternate Text',
            'variable_name' => 'image_alt',
            'is_required' => false,
            'is_active' => true
        ]);
        $menuImageAlt->upsertOptionValue(OptionValueConstants::STRING, '');
        $menuImageAlt->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $menuImageClass */
        $menuImageClass = $menu->componentOptions()->create([
            'name' => 'Image Class',
            'variable_name' => 'image_class',
            'is_required' => false,
            'is_active' => true
        ]);
        $menuImageClass->upsertOptionValue(OptionValueConstants::STRING, '');
        $menuImageClass->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $menuImageCaption */
        $menuImageCaption = $menu->componentOptions()->create([
            'name' => 'Image Caption',
            'variable_name' => 'image_caption',
            'is_required' => false,
            'is_active' => true
        ]);
        $menuImageCaption->upsertOptionValue(OptionValueConstants::STRING, '');
        $menuImageCaption->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $menuImageTitle */
        $menuImageTitle = $menu->componentOptions()->create([
            'name' => 'Image Title',
            'variable_name' => 'image_title',
            'is_required' => false,
            'is_active' => true
        ]);
        $menuImageTitle->upsertOptionValue(OptionValueConstants::STRING, '');
        $menuImageTitle->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $menuSubMenus */
        $menuSubMenus = $menu->componentOptions()->create([
            'name' => 'Sub Menus',
            'variable_name' => 'sub_menus',
            'is_required' => false,
            'is_active' => true
        ]);
        $menuSubMenus->upsertOptionValue(OptionValueConstants::STRING);
        $menuSubMenus->upsertOptionElementType(OptionElementTypeConstants::CONTROL_LIST, [
            'selectedControlId' => $menu->getKey(),
            'selectedControlName' => $menu->name
        ]);

        // Menu Group
        /** @var Component $menuGroup */
        $menuGroup = Component::create([
            'name' => 'Menu Group',
            'variable_name' => 'menu_group'
        ]);
        /** @var \App\Api\Models\ComponentOption $menuGroupTitle */
        $menuGroupTitle = $menuGroup->componentOptions()->create([
            'name' => 'Title',
            'variable_name' => 'title',
            'is_required' => false,
            'is_active' => true
        ]);
        $menuGroupTitle->upsertOptionValue(OptionValueConstants::STRING, '');
        $menuGroupTitle->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $menuGroupMenus */
        $menuGroupMenus = $menuGroup->componentOptions()->create([
            'name' => 'Menus',
            'variable_name' => 'menus',
            'is_required' => true,
            'is_active' => true
        ]);
        $menuGroupMenus->upsertOptionValue(OptionValueConstants::STRING);
        $menuGroupMenus->upsertOptionElementType(OptionElementTypeConstants::CONTROL_LIST, [
            'selectedControlId' => $menu->getKey(),
            'selectedControlName' => $menu->name,
            'propStructure' => [
                [
                    'prop_id' => $menuUrl->getKey(),
                    'name' => $menuUrl->name,
                    'variable_name' => $menuUrl->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode(['helper' => 'url']),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '#',
                    'is_required' => $menuUrl->is_required
                ],
                [
                    'prop_id' => $menuLabel->getKey(),
                    'name' => $menuLabel->name,
                    'variable_name' => $menuLabel->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode(['helper' => '']),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $menuLabel->is_required
                ],
                [
                    'prop_id' => $menuTarget->getKey(),
                    'name' => $menuTarget->name,
                    'variable_name' => $menuTarget->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode([
                        [
                            'text' => 'Same page',
                            'value' => '_self'
                        ],
                        [
                            'text' => 'New page',
                            'value' => '_blank'
                        ]
                    ]),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $menuTarget->is_required
                ],
                [
                    'prop_id' => $menuImage->getKey(),
                    'name' => $menuImage->name,
                    'variable_name' => $menuImage->variable_name,
                    'element_type' => OptionElementTypeConstants::FILE_UPLOAD,
                    'element_value' => json_encode([
                        'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
                        'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
                        'imageSizeOption' => [
                            'width' => '',
                            'height' => '',
                            'ratio' => '',
                            'size' => ''
                        ]
                    ]),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $menuImage->is_required
                ],
                [
                    'prop_id' => $menuImageAlt->getKey(),
                    'name' => $menuImageAlt->name,
                    'variable_name' => $menuImageAlt->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode(['helper' => '']),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $menuImageAlt->is_required
                ],
                [
                    'prop_id' => $menuImageClass->getKey(),
                    'name' => $menuImageClass->name,
                    'variable_name' => $menuImageClass->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode(['helper' => '']),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $menuImageClass->is_required
                ],
                [
                    'prop_id' => $menuImageCaption->getKey(),
                    'name' => $menuImageCaption->name,
                    'variable_name' => $menuImageCaption->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode(['helper' => '']),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $menuImageCaption->is_required
                ],
                [
                    'prop_id' => $menuImageTitle->getKey(),
                    'name' => $menuImageTitle->name,
                    'variable_name' => $menuImageTitle->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode(['helper' => '']),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $menuImageTitle->is_required
                ],
                [
                    'prop_id' => $menuSubMenus->getKey(),
                    'name' => $menuSubMenus->name,
                    'variable_name' => $menuSubMenus->variable_name,
                    'element_type' => OptionElementTypeConstants::CONTROL_LIST,
                    'element_value' => json_encode([
                        'selectedControlId' => $menu->getKey(),
                        'selectedControlName' => $menu->name
                    ]),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $menuSubMenus->is_required
                ]
            ]
        ]);

        // Url
        /** @var Component $url */
        $url = Component::create([
            'name' => 'Url',
            'variable_name' => 'url'
        ]);
        /** @var \App\Api\Models\ComponentOption $urlLink */
        $urlLink = $url->componentOptions()->create([
            'name' => 'Link',
            'variable_name' => 'link',
            'is_required' => true,
            'is_active' => true
        ]);
        $urlLink->upsertOptionValue(OptionValueConstants::STRING, '#');
        $urlLink->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => 'url'
        ]);

        /** @var \App\Api\Models\ComponentOption $urlTarget */
        $urlTarget = $url->componentOptions()->create([
            'name' => 'Target',
            'variable_name' => 'target',
            'is_required' => true,
            'is_active' => true
        ]);
        $urlTarget->upsertOptionValue(OptionValueConstants::STRING, '_self');
        $urlTarget->upsertOptionElementType(OptionElementTypeConstants::DROPDOWN, [
            [
                'text' => 'Same page',
                'value' => '_self'
            ],
            [
                'text' => 'New page',
                'value' => '_blank'
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $urlLabel */
        $urlLabel = $url->componentOptions()->create([
            'name' => 'Label',
            'variable_name' => 'label',
            'is_required' => false,
            'is_active' => true
        ]);
        $urlLabel->upsertOptionValue(OptionValueConstants::STRING, '');
        $urlLabel->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $urlClass */
        $urlClass = $url->componentOptions()->create([
            'name' => 'Class',
            'variable_name' => 'class',
            'is_required' => false,
            'is_active' => true
        ]);
        $urlClass->upsertOptionValue(OptionValueConstants::STRING, '');
        $urlClass->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $urlTitle */
        $urlTitle = $url->componentOptions()->create([
            'name' => 'Title',
            'variable_name' => 'title',
            'is_required' => false,
            'is_active' => true
        ]);
        $urlTitle->upsertOptionValue(OptionValueConstants::STRING, '');
        $urlTitle->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);
        
        // Image
        /** @var Component $image */
        $image = Component::create([
            'name' => 'Image',
            'variable_name' => 'image'
        ]);
        /** @var \App\Api\Models\ComponentOption $imageFile */
        $imageFile = $image->componentOptions()->create([
            'name' => 'File',
            'variable_name' => 'file',
            'is_required' => true,
            'is_active' => true
        ]);
        $imageFile->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageFile->upsertOptionElementType(OptionElementTypeConstants::FILE_UPLOAD, [
            'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'imageSizeOption' => [
                'width' => '',
                'height' => '',
                'ratio' => '',
                'size' => ''
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $imageAlt */
        $imageAlt = $image->componentOptions()->create([
            'name' => 'Alternate Text',
            'variable_name' => 'alt',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageAlt->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageAlt->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageClass */
        $imageClass = $image->componentOptions()->create([
            'name' => 'Class',
            'variable_name' => 'class',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageClass->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageClass->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageCaption */
        $imageCaption = $image->componentOptions()->create([
            'name' => 'Caption',
            'variable_name' => 'caption',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageCaption->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageCaption->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageTitle */
        $imageTitle = $image->componentOptions()->create([
            'name' => 'Title',
            'variable_name' => 'title',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageTitle->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageTitle->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageLink */
        $imageLink = $image->componentOptions()->create([
            'name' => 'Link',
            'variable_name' => 'link',
            'is_required' => true,
            'is_active' => true
        ]);
        $imageLink->upsertOptionValue(OptionValueConstants::STRING, '#');
        $imageLink->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => 'url'
        ]);

        /** @var \App\Api\Models\ComponentOption $imageLinkTarget */
        $imageLinkTarget = $image->componentOptions()->create([
            'name' => 'Link Target',
            'variable_name' => 'link_target',
            'is_required' => true,
            'is_active' => true
        ]);
        $imageLinkTarget->upsertOptionValue(OptionValueConstants::STRING, '_self');
        $imageLinkTarget->upsertOptionElementType(OptionElementTypeConstants::DROPDOWN, [
            [
                'text' => 'Same page',
                'value' => '_self'
            ],
            [
                'text' => 'New page',
                'value' => '_blank'
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $imageLinkLabel */
        $imageLinkLabel = $image->componentOptions()->create([
            'name' => 'Link Label',
            'variable_name' => 'link_label',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageLinkLabel->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageLinkLabel->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $linkLinkClass */
        $linkLinkClass = $image->componentOptions()->create([
            'name' => 'Link Class',
            'variable_name' => 'link_class',
            'is_required' => false,
            'is_active' => true
        ]);
        $linkLinkClass->upsertOptionValue(OptionValueConstants::STRING, '');
        $linkLinkClass->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageLinkTitle */
        $imageLinkTitle = $image->componentOptions()->create([
            'name' => 'Link Title',
            'variable_name' => 'link_title',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageLinkTitle->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageLinkTitle->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);
        
        // Image with thumbnail options
        /** @var Component $imageWithThumbnailOptions */
        $imageWithThumbnailOptions = Component::create([
            'name' => 'Image with Thumbnail Options',
            'variable_name' => 'image_with_thumbnail_options'
        ]);
        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsFile */
        $imageWithThumbnailOptionsFile = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'File',
            'variable_name' => 'file',
            'is_required' => true,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsFile->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageWithThumbnailOptionsFile->upsertOptionElementType(OptionElementTypeConstants::FILE_UPLOAD, [
            'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'imageSizeOption' => [
                'width' => '',
                'height' => '',
                'ratio' => '',
                'size' => ''
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsAlt */
        $imageWithThumbnailOptionsAlt = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Alternate Text',
            'variable_name' => 'alt',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsAlt->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageWithThumbnailOptionsAlt->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsClass */
        $imageWithThumbnailOptionsClass = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Class',
            'variable_name' => 'class',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsClass->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageWithThumbnailOptionsClass->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsCaption */
        $imageWithThumbnailOptionsCaption = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Caption',
            'variable_name' => 'caption',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsCaption->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageWithThumbnailOptionsCaption->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsTitle */
        $imageWithThumbnailOptionsTitle = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Title',
            'variable_name' => 'title',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsTitle->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageWithThumbnailOptionsTitle->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsLink */
        $imageWithThumbnailOptionsLink = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Link',
            'variable_name' => 'link',
            'is_required' => true,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsLink->upsertOptionValue(OptionValueConstants::STRING, '#');
        $imageWithThumbnailOptionsLink->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => 'url'
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsLinkTarget */
        $imageWithThumbnailOptionsLinkTarget = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Link Target',
            'variable_name' => 'link_target',
            'is_required' => true,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsLinkTarget->upsertOptionValue(OptionValueConstants::STRING, '_self');
        $imageWithThumbnailOptionsLinkTarget->upsertOptionElementType(OptionElementTypeConstants::DROPDOWN, [
            [
                'text' => 'Same page',
                'value' => '_self'
            ],
            [
                'text' => 'New page',
                'value' => '_blank'
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsLinkLabel */
        $imageWithThumbnailOptionsLinkLabel = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Link Label',
            'variable_name' => 'link_label',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsLinkLabel->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageWithThumbnailOptionsLinkLabel->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $linkLinkClass */
        $linkLinkClass = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Link Class',
            'variable_name' => 'link_class',
            'is_required' => false,
            'is_active' => true
        ]);
        $linkLinkClass->upsertOptionValue(OptionValueConstants::STRING, '');
        $linkLinkClass->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsLinkTitle */
        $imageWithThumbnailOptionsLinkTitle = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Link Title',
            'variable_name' => 'link_title',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsLinkTitle->upsertOptionValue(OptionValueConstants::STRING, '');
        $imageWithThumbnailOptionsLinkTitle->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsLinkWidth */
        $imageWithThumbnailOptionsLinkWidth = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Width',
            'variable_name' => 'width',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsLinkWidth->upsertOptionValue(OptionValueConstants::STRING, 1024);
        $imageWithThumbnailOptionsLinkWidth->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsLinkHeight */
        $imageWithThumbnailOptionsLinkHeight = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Height',
            'variable_name' => 'height',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsLinkHeight->upsertOptionValue(OptionValueConstants::STRING, 768);
        $imageWithThumbnailOptionsLinkHeight->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsLinkQuadrant */
        $imageWithThumbnailOptionsLinkQuadrant = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Quadrant',
            'variable_name' => 'quadrant',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsLinkQuadrant->upsertOptionValue(OptionValueConstants::STRING, 'C');
        $imageWithThumbnailOptionsLinkQuadrant->upsertOptionElementType(OptionElementTypeConstants::DROPDOWN, [
            [
                'text' => 'Top',
                'value' => 'T'
            ],
            [
                'text' => 'Bottom',
                'value' => 'B'
            ],
            [
                'text' => 'Left',
                'value' => 'L'
            ],
            [
                'text' => 'Right',
                'value' => 'R'
            ],
            [
                'text' => 'Center',
                'value' => 'C'
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $imageWithThumbnailOptionsResize */
        $imageWithThumbnailOptionsResize = $imageWithThumbnailOptions->componentOptions()->create([
            'name' => 'Resize?',
            'variable_name' => 'resize',
            'is_required' => false,
            'is_active' => true
        ]);
        $imageWithThumbnailOptionsResize->upsertOptionValue(OptionValueConstants::STRING, 1);
        $imageWithThumbnailOptionsResize->upsertOptionElementType(OptionElementTypeConstants::CHECKBOX, [
            'checkedValue' => '',
            'checkedText' => 'Yes',
            'uncheckedText' => 'No'
        ]);

        // Code
        /** @var Component $code */
        $code = Component::create([
            'name' => 'Code',
            'variable_name' => 'code'
        ]);
        /** @var \App\Api\Models\ComponentOption $codeValue */
        $codeValue = $code->componentOptions()->create([
            'name' => 'Value',
            'variable_name' => 'value',
            'is_required' => true,
            'is_active' => true
        ]);
        $codeValue->upsertOptionValue(OptionValueConstants::STRING, '');
        $codeValue->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
            'scriptFormat' => true
        ]);

        // Text
        /** @var Component $text */
        $text = Component::create([
            'name' => 'Text',
            'variable_name' => 'text'
        ]);
        /** @var \App\Api\Models\ComponentOption $textValue */
        $textValue = $text->componentOptions()->create([
            'name' => 'Value',
            'variable_name' => 'value',
            'is_required' => true,
            'is_active' => true
        ]);
        $textValue->upsertOptionValue(OptionValueConstants::STRING, '');
        $textValue->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
            'scriptFormat' => false
        ]);

        // Rich Text
        /** @var Component $richText */
        $richText = Component::create([
            'name' => 'Rich Text',
            'variable_name' => 'rich_text'
        ]);
        /** @var \App\Api\Models\ComponentOption $richTextValue */
        $richTextValue = $richText->componentOptions()->create([
            'name' => 'Value',
            'variable_name' => 'value',
            'is_required' => true,
            'is_active' => true
        ]);
        $richTextValue->upsertOptionValue(OptionValueConstants::STRING, '');
        $richTextValue->upsertOptionElementType(OptionElementTypeConstants::RICHTEXT_EDITOR, []);

        // Title and content
        /** @var Component $titleAndContent */
        $titleAndContent = Component::create([
            'name' => 'Title and Content',
            'variable_name' => 'title_and_content'
        ]);
        /** @var \App\Api\Models\ComponentOption $titleAndContentTitle */
        $titleAndContentTitle = $titleAndContent->componentOptions()->create([
            'name' => 'Title',
            'variable_name' => 'title',
            'is_required' => false,
            'is_active' => true
        ]);
        $titleAndContentTitle->upsertOptionValue(OptionValueConstants::STRING, '');
        $titleAndContentTitle->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $titleAndContentContent */
        $titleAndContentContent = $titleAndContent->componentOptions()->create([
            'name' => 'Content',
            'variable_name' => 'content'
        ]);
        $titleAndContentContent->upsertOptionValue(OptionValueConstants::STRING, '');
        $titleAndContentContent->upsertOptionElementType(OptionElementTypeConstants::RICHTEXT_EDITOR, []);
        
        // Form Property
        /** @var Component $formProperty */
        $formProperty = Component::create([
            'name' => 'Form Property',
            'variable_name' => 'form_property'
        ]);
        /** @var \App\Api\Models\ComponentOption $formPropertyName */
        $formPropertyName = $formProperty->componentOptions()->create([
            'name' => 'Name',
            'variable_name' => 'name',
            'is_required' => true,
            'is_active' => true
        ]);
        $formPropertyName->upsertOptionValue(OptionValueConstants::STRING, '');
        $formPropertyName->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $formPropertyLabel */
        $formPropertyLabel = $formProperty->componentOptions()->create([
            'name' => 'Label',
            'variable_name' => 'label',
            'is_required' => false,
            'is_active' => true
        ]);
        $formPropertyLabel->upsertOptionValue(OptionValueConstants::STRING, '');
        $formPropertyLabel->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $formPropertyPlaceholder */
        $formPropertyPlaceholder = $formProperty->componentOptions()->create([
            'name' => 'Placeholder',
            'variable_name' => 'placeholder',
            'is_required' => false,
            'is_active' => true
        ]);
        $formPropertyPlaceholder->upsertOptionValue(OptionValueConstants::STRING, '');
        $formPropertyPlaceholder->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $formPropertyDefaultValue */
        $formPropertyDefaultValue = $formProperty->componentOptions()->create([
            'name' => 'Default Value',
            'variable_name' => 'default_value',
            'is_required' => false,
            'is_active' => true
        ]);
        $formPropertyDefaultValue->upsertOptionValue(OptionValueConstants::STRING, '');
        $formPropertyDefaultValue->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $formPropertyIsRequired */
        $formPropertyIsRequired = $formProperty->componentOptions()->create([
            'name' => 'Is Required?',
            'variable_name' => 'is_required',
            'is_required' => false,
            'is_active' => true
        ]);
        $formPropertyIsRequired->upsertOptionValue(OptionValueConstants::INTEGER, 0);
        $formPropertyIsRequired->upsertOptionElementType(OptionElementTypeConstants::CHECKBOX, [
            'checkedValue' => '',
            'checkedText' => 'Yes',
            'uncheckedText' => 'No'
        ]);

        /** @var \App\Api\Models\ComponentOption $formPropertyType */
        $formPropertyType = $formProperty->componentOptions()->create([
            'name' => 'Type',
            'variable_name' => 'type',
            'is_required' => true,
            'is_active' => true
        ]);
        $formPropertyType->upsertOptionValue(OptionValueConstants::STRING, 'text');
        $formPropertyType->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        // Form
        /** @var Component $form */
        $form = Component::create([
            'name' => 'Form',
            'variable_name' => 'form'
        ]);
        /** @var \App\Api\Models\ComponentOption $formFormType */
        $formFormType = $form->componentOptions()->create([
            'name' => 'Form Type',
            'variable_name' => 'form_type',
            'is_required' => true,
            'is_active' => true
        ]);
        $formFormType->upsertOptionValue(OptionValueConstants::STRING, 'form');
        $formFormType->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $formFormProperties */
        $formFormProperties = $form->componentOptions()->create([
            'name' => 'Form Properties',
            'variable_name' => 'form_properties',
            'is_required' => true,
            'is_active' => true
        ]);
        $formFormProperties->upsertOptionValue(OptionValueConstants::STRING);
        $formFormProperties->upsertOptionElementType(OptionElementTypeConstants::CONTROL_LIST, [
            'selectedControlId' => $formProperty->getKey(),
            'selectedControlName' => $formProperty->name,
            'propStructure' => [
                [
                    'prop_id' => $formPropertyName->getKey(),
                    'name' => $formPropertyName->name,
                    'variable_name' => $formPropertyName->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode(['helper' => '']),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $formPropertyName->is_required
                ],
                [
                    'prop_id' => $formPropertyLabel->getKey(),
                    'name' => $formPropertyLabel->name,
                    'variable_name' => $formPropertyLabel->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode(['helper' => '']),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $formPropertyLabel->is_required
                ],
                [
                    'prop_id' => $formPropertyPlaceholder->getKey(),
                    'name' => $formPropertyPlaceholder->name,
                    'variable_name' => $formPropertyPlaceholder->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode(['helper' => '']),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $formPropertyPlaceholder->is_required
                ],
                [
                    'prop_id' => $formPropertyDefaultValue->getKey(),
                    'name' => $formPropertyDefaultValue->name,
                    'variable_name' => $formPropertyDefaultValue->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode(['helper' => '']),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $formPropertyDefaultValue->is_required
                ],
                [
                    'prop_id' => $formPropertyIsRequired->getKey(),
                    'name' => $formPropertyIsRequired->name,
                    'variable_name' => $formPropertyIsRequired->variable_name,
                    'element_type' => OptionElementTypeConstants::CHECKBOX,
                    'element_value' => json_encode([
                        'checkedValue' => '',
                        'checkedText' => 'Yes',
                        'uncheckedText' => 'No'
                    ]),
                    'option_type' => OptionValueConstants::INTEGER,
                    'option_value' => 0,
                    'is_required' => $formPropertyIsRequired->is_required
                ],
                [
                    'prop_id' => $formPropertyType->getKey(),
                    'name' => $formPropertyType->name,
                    'variable_name' => $formPropertyType->variable_name,
                    'element_type' => OptionElementTypeConstants::TEXTBOX,
                    'element_value' => json_encode(['helper' => '']),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $formPropertyType->is_required
                ]
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $formTargetEmails */
        $formTargetEmails = $form->componentOptions()->create([
            'name' => 'Target Emails',
            'variable_name' => 'target_emails',
            'is_required' => true,
            'is_active' => true
        ]);
        $formTargetEmails->upsertOptionValue(OptionValueConstants::STRING, null);
        $formTargetEmails->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
            'scriptFormat' => false
        ]);

        /** @var \App\Api\Models\ComponentOption $formTargetNotifySubject */
        $formTargetNotifySubject = $form->componentOptions()->create([
            'name' => 'Target Notify Subject',
            'variable_name' => 'target_notify_subject',
            'is_required' => false,
            'is_active' => true
        ]);
        $formTargetNotifySubject->upsertOptionValue(OptionValueConstants::STRING, null);
        $formTargetNotifySubject->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $formTargetNotifyTemplate */
        $formTargetNotifyTemplate = $form->componentOptions()->create([
            'name' => 'Target Notify Template',
            'variable_name' => 'target_notify_template',
            'is_required' => false,
            'is_active' => true
        ]);
        $formTargetNotifyTemplate->upsertOptionValue(OptionValueConstants::STRING, null);
        $formTargetNotifyTemplate->upsertOptionElementType(OptionElementTypeConstants::RICHTEXT_EDITOR, []);

        /** @var \App\Api\Models\ComponentOption $formTargetEmailCC */
        $formTargetEmailCC = $form->componentOptions()->create([
            'name' => 'Target Email CC',
            'variable_name' => 'target_email_cc',
            'is_required' => false,
            'is_active' => true
        ]);
        $formTargetEmailCC->upsertOptionValue(OptionValueConstants::STRING, null);
        $formTargetEmailCC->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
            'scriptFormat' => false
        ]);

        /** @var \App\Api\Models\ComponentOption $formTargetEmailBCC */
        $formTargetEmailBCC = $form->componentOptions()->create([
            'name' => 'Target Email BCC',
            'variable_name' => 'target_email_bcc',
            'is_required' => false,
            'is_active' => true
        ]);
        $formTargetEmailBCC->upsertOptionValue(OptionValueConstants::STRING, null);
        $formTargetEmailBCC->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
            'scriptFormat' => false
        ]);

        /** @var \App\Api\Models\ComponentOption $formSendToTargetEmails */
        $formSendToTargetEmails = $form->componentOptions()->create([
            'name' => 'Send to Target Emails',
            'variable_name' => 'send_to_target_emails',
            'is_required' => true,
            'is_active' => true
        ]);
        $formSendToTargetEmails->upsertOptionValue(OptionValueConstants::INTEGER, 1);
        $formSendToTargetEmails->upsertOptionElementType(OptionElementTypeConstants::CHECKBOX, [
            'checkedValue' => '',
            'checkedText' => 'Yes',
            'uncheckedText' => 'No'
        ]);

        /** @var \App\Api\Models\ComponentOption $formUserNotification */
        $formUserNotification = $form->componentOptions()->create([
            'name' => 'User notification?',
            'variable_name' => 'user_notification',
            'is_required' => true,
            'is_active' => true
        ]);
        $formUserNotification->upsertOptionValue(OptionValueConstants::INTEGER, 0);
        $formUserNotification->upsertOptionElementType(OptionElementTypeConstants::CHECKBOX, [
            'checkedValue' => '',
            'checkedText' => 'Yes',
            'uncheckedText' => 'No'
        ]);

        /** @var \App\Api\Models\ComponentOption $formUserNotifySubject */
        $formUserNotifySubject = $form->componentOptions()->create([
            'name' => 'User Notify Subject',
            'variable_name' => 'user_notify_subject',
            'is_required' => false,
            'is_active' => true
        ]);
        $formUserNotifySubject->upsertOptionValue(OptionValueConstants::STRING, null);
        $formUserNotifySubject->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $formUserNotifyTemplate */
        $formUserNotifyTemplate = $form->componentOptions()->create([
            'name' => 'User Notify Template',
            'variable_name' => 'user_notify_template',
            'is_required' => false,
            'is_active' => true
        ]);
        $formUserNotifyTemplate->upsertOptionValue(OptionValueConstants::STRING, null);
        $formUserNotifyTemplate->upsertOptionElementType(OptionElementTypeConstants::RICHTEXT_EDITOR, []);

        /** @var \App\Api\Models\ComponentOption $formSubmitButtonLabel */
        $formSubmitButtonLabel = $form->componentOptions()->create([
            'name' => 'Submit Button Label',
            'variable_name' => 'submit_button_label',
            'is_required' => false,
            'is_active' => true
        ]);
        $formSubmitButtonLabel->upsertOptionValue(OptionValueConstants::STRING, 'submit');
        $formSubmitButtonLabel->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $formSuccessMessage */
        $formSuccessMessage = $form->componentOptions()->create([
            'name' => 'Success Message',
            'variable_name' => 'success_message',
            'is_required' => false,
            'is_active' => true
        ]);
        $formSuccessMessage->upsertOptionValue(OptionValueConstants::STRING, '');
        $formSuccessMessage->upsertOptionElementType(OptionElementTypeConstants::RICHTEXT_EDITOR, []);

        /** @var \App\Api\Models\ComponentOption $formFailureMessage */
        $formFailureMessage = $form->componentOptions()->create([
            'name' => 'Failure Message',
            'variable_name' => 'failure_message',
            'is_required' => false,
            'is_active' => true
        ]);
        $formFailureMessage->upsertOptionValue(OptionValueConstants::STRING, '');
        $formFailureMessage->upsertOptionElementType(OptionElementTypeConstants::RICHTEXT_EDITOR, []);

        // Metadata
        /** @var Component $metadata */
        $metadata = Component::create([
            'name' => 'Metadata',
            'variable_name' => 'metadata'
        ]);
        /** @var \App\Api\Models\ComponentOption $metadataTitle */
        $metadataTitle = $metadata->componentOptions()->create([
            'name' => 'Title',
            'variable_name' => 'title',
            'is_required' => true,
            'is_active' => true
        ]);
        $metadataTitle->upsertOptionValue(OptionValueConstants::STRING, '');
        $metadataTitle->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $metadataDescription */
        $metadataDescription = $metadata->componentOptions()->create([
            'name' => 'Description',
            'variable_name' => 'description',
            'is_required' => false,
            'is_active' => true
        ]);
        $metadataDescription->upsertOptionValue(OptionValueConstants::STRING, '');
        $metadataDescription->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
            'scriptFormat' => false
        ]);

        /** @var \App\Api\Models\ComponentOption $metadataKeywords */
        $metadataKeywords = $metadata->componentOptions()->create([
            'name' => 'Keywords',
            'variable_name' => 'keywords',
            'is_required' => false,
            'is_active' => true
        ]);
        $metadataKeywords->upsertOptionValue(OptionValueConstants::STRING, '');
        $metadataKeywords->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
            'scriptFormat' => false
        ]);

        /** @var \App\Api\Models\ComponentOption $metadataAuthor */
        $metadataAuthor = $metadata->componentOptions()->create([
            'name' => 'Author',
            'variable_name' => 'author',
            'is_required' => false,
            'is_active' => true
        ]);
        $metadataAuthor->upsertOptionValue(OptionValueConstants::STRING, '');
        $metadataAuthor->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $metadataCanonicalUrl */
        $metadataCanonicalUrl = $metadata->componentOptions()->create([
            'name' => 'Canonical Url',
            'variable_name' => 'canonical_url',
            'is_required' => false,
            'is_active' => true
        ]);
        $metadataCanonicalUrl->upsertOptionValue(OptionValueConstants::STRING, '');
        $metadataCanonicalUrl->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => 'url'
        ]);

        // Google Plus Metadata
        /** @var Component $googlePlusMetadata */
        $googlePlusMetadata = Component::create([
            'name' => 'Google Plus Metadata',
            'variable_name' => 'google_plus_metadata'
        ]);
        /** @var \App\Api\Models\ComponentOption $googlePlusMetadataName */
        $googlePlusMetadataName = $googlePlusMetadata->componentOptions()->create([
            'name' => 'Name',
            'variable_name' => 'name',
            'is_required' => true,
            'is_active' => true
        ]);
        $googlePlusMetadataName->upsertOptionValue(OptionValueConstants::STRING, '');
        $googlePlusMetadataName->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $googlePlusMetadataDescription */
        $googlePlusMetadataDescription = $googlePlusMetadata->componentOptions()->create([
            'name' => 'Description',
            'variable_name' => 'description',
            'is_required' => false,
            'is_active' => true
        ]);
        $googlePlusMetadataDescription->upsertOptionValue(OptionValueConstants::STRING, '');
        $googlePlusMetadataDescription->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
            'scriptFormat' => false
        ]);

        /** @var \App\Api\Models\ComponentOption $googlePlusMetadataImage */
        $googlePlusMetadataImage = $googlePlusMetadata->componentOptions()->create([
            'name' => 'Image',
            'variable_name' => 'image',
            'is_required' => false,
            'is_active' => true
        ]);
        $googlePlusMetadataImage->upsertOptionValue(OptionValueConstants::STRING, '');
        $googlePlusMetadataImage->upsertOptionElementType(OptionElementTypeConstants::FILE_UPLOAD, [
            'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'imageSizeOption' => [
                'width' => '',
                'height' => '',
                'ratio' => '',
                'size' => ''
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $googlePlusMetadataUrl */
        $googlePlusMetadataUrl = $googlePlusMetadata->componentOptions()->create([
            'name' => 'Url',
            'variable_name' => 'url',
            'is_required' => false,
            'is_active' => true
        ]);
        $googlePlusMetadataUrl->upsertOptionValue(OptionValueConstants::STRING, '');
        $googlePlusMetadataUrl->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => 'url'
        ]);

        /** @var \App\Api\Models\ComponentOption $googlePlusMetadataAlternateName */
        $googlePlusMetadataAlternateName = $googlePlusMetadata->componentOptions()->create([
            'name' => 'Alternate Name',
            'variable_name' => 'alternate_name',
            'is_required' => false,
            'is_active' => true
        ]);
        $googlePlusMetadataAlternateName->upsertOptionValue(OptionValueConstants::STRING, '');
        $googlePlusMetadataAlternateName->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        // Open Graph Metadata
        /** @var Component $openGraphMetadata */
        $openGraphMetadata = Component::create([
            'name' => 'Open Graph Metadata',
            'variable_name' => 'open_graph_metadata'
        ]);
        /** @var \App\Api\Models\ComponentOption $openGraphMetadataType */
        $openGraphMetadataType = $openGraphMetadata->componentOptions()->create([
            'name' => 'Type',
            'variable_name' => 'type',
            'is_required' => true,
            'is_active' => true
        ]);
        $openGraphMetadataType->upsertOptionValue(OptionValueConstants::STRING, 'article');
        $openGraphMetadataType->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $openGraphMetadataTitle */
        $openGraphMetadataTitle = $openGraphMetadata->componentOptions()->create([
            'name' => 'Title',
            'variable_name' => 'title',
            'is_required' => true,
            'is_active' => true
        ]);
        $openGraphMetadataTitle->upsertOptionValue(OptionValueConstants::STRING, '');
        $openGraphMetadataTitle->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $openGraphMetadataDescription */
        $openGraphMetadataDescription = $openGraphMetadata->componentOptions()->create([
            'name' => 'Description',
            'variable_name' => 'description',
            'is_required' => false,
            'is_active' => true
        ]);
        $openGraphMetadataDescription->upsertOptionValue(OptionValueConstants::STRING, '');
        $openGraphMetadataDescription->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
            'scriptFormat' => false
        ]);

        /** @var \App\Api\Models\ComponentOption $openGraphMetadataImage */
        $openGraphMetadataImage = $openGraphMetadata->componentOptions()->create([
            'name' => 'Image',
            'variable_name' => 'image',
            'is_required' => false,
            'is_active' => true
        ]);
        $openGraphMetadataImage->upsertOptionValue(OptionValueConstants::STRING, '');
        $openGraphMetadataImage->upsertOptionElementType(OptionElementTypeConstants::FILE_UPLOAD, [
            'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'imageSizeOption' => [
                'width' => '',
                'height' => '',
                'ratio' => '',
                'size' => ''
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $openGraphMetadataUrl */
        $openGraphMetadataUrl = $openGraphMetadata->componentOptions()->create([
            'name' => 'Url',
            'variable_name' => 'url',
            'is_required' => false,
            'is_active' => true
        ]);
        $openGraphMetadataUrl->upsertOptionValue(OptionValueConstants::STRING, '');
        $openGraphMetadataUrl->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => 'url'
        ]);

        /** @var \App\Api\Models\ComponentOption $openGraphMetadataSiteName */
        $openGraphMetadataSiteName = $openGraphMetadata->componentOptions()->create([
            'name' => 'Site Name',
            'variable_name' => 'site_name',
            'is_required' => false,
            'is_active' => true
        ]);
        $openGraphMetadataSiteName->upsertOptionValue(OptionValueConstants::STRING, '');
        $openGraphMetadataSiteName->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $openGraphMetadataFacebookAdminID */
        $openGraphMetadataFacebookAdminID = $openGraphMetadata->componentOptions()->create([
            'name' => 'Facebook Admin ID',
            'variable_name' => 'facebook_admin_id',
            'is_required' => false,
            'is_active' => true
        ]);
        $openGraphMetadataFacebookAdminID->upsertOptionValue(OptionValueConstants::STRING, '');
        $openGraphMetadataFacebookAdminID->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        // Twitter Card Metadata
        /** @var Component $twitterCardMetadata */
        $twitterCardMetadata = Component::create([
            'name' => 'Twitter Card Metadata',
            'variable_name' => 'twitter_card_metadata'
        ]);
        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataCardType */
        $twitterCardMetadataCardType = $twitterCardMetadata->componentOptions()->create([
            'name' => 'Card Type',
            'variable_name' => 'card_type',
            'is_required' => true,
            'is_active' => true
        ]);
        $twitterCardMetadataCardType->upsertOptionValue(OptionValueConstants::STRING, 'summary');
        $twitterCardMetadataCardType->upsertOptionElementType(OptionElementTypeConstants::DROPDOWN, [
            [
                'text' => 'Summary',
                'value' => 'summary'
            ],
            [
                'text' => 'Summary with large image',
                'value' => 'summary_large_image'
            ],
            [
                'text' => 'App',
                'value' => 'app'
            ],
            [
                'text' => 'Player',
                'value' => 'player'
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataTitle */
        $twitterCardMetadataTitle = $twitterCardMetadata->componentOptions()->create([
            'name' => 'Title',
            'variable_name' => 'title',
            'is_required' => true,
            'is_active' => true
        ]);
        $twitterCardMetadataTitle->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataTitle->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataDescription */
        $twitterCardMetadataDescription = $twitterCardMetadata->componentOptions()->create([
            'name' => 'Description',
            'variable_name' => 'description',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataDescription->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataDescription->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
            'scriptFormat' => false
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataImage */
        $twitterCardMetadataImage = $twitterCardMetadata->componentOptions()->create([
            'name' => 'Image',
            'variable_name' => 'image',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataImage->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataImage->upsertOptionElementType(OptionElementTypeConstants::FILE_UPLOAD, [
            'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'imageSizeOption' => [
                'width' => '',
                'height' => '',
                'ratio' => '',
                'size' => ''
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataImageAlt */
        $twitterCardMetadataImageAlt = $twitterCardMetadata->componentOptions()->create([
            'name' => 'Image Alternate Text',
            'variable_name' => 'image_alt',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataImageAlt->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataImageAlt->upsertOptionElementType(OptionElementTypeConstants::MULTILINE_TEXT, [
            'scriptFormat' => false
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataSite */
        $twitterCardMetadataSite = $twitterCardMetadata->componentOptions()->create([
            'name' => '@Site',
            'variable_name' => 'site',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataSite->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataSite->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataCreator */
        $twitterCardMetadataCreator = $twitterCardMetadata->componentOptions()->create([
            'name' => '@Creator',
            'variable_name' => 'creator',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataCreator->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataCreator->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);
        
        //App
        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataAppCountry */
        $twitterCardMetadataAppCountry = $twitterCardMetadata->componentOptions()->create([
            'name' => 'App Country',
            'variable_name' => 'app_country',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataAppCountry->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataAppCountry->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);
        
        // iPhone
        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataAppIphoneID */
        $twitterCardMetadataAppIphoneID = $twitterCardMetadata->componentOptions()->create([
            'name' => 'App iPhone ID',
            'variable_name' => 'app_iphone_id',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataAppIphoneID->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataAppIphoneID->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataAppIphoneUrl */
        $twitterCardMetadataAppIphoneUrl = $twitterCardMetadata->componentOptions()->create([
            'name' => 'App iPhone Url',
            'variable_name' => 'app_iphone_url',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataAppIphoneUrl->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataAppIphoneUrl->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);
        
        // iPad
        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataAppIpadName */
        $twitterCardMetadataAppIpadName = $twitterCardMetadata->componentOptions()->create([
            'name' => 'App iPad Name',
            'variable_name' => 'app_ipad_name',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataAppIpadName->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataAppIpadName->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataAppIpadID */
        $twitterCardMetadataAppIpadID = $twitterCardMetadata->componentOptions()->create([
            'name' => 'App iPad ID',
            'variable_name' => 'app_ipad_id',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataAppIpadID->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataAppIpadID->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataAppIpadUrl */
        $twitterCardMetadataAppIpadUrl = $twitterCardMetadata->componentOptions()->create([
            'name' => 'App iPad Url',
            'variable_name' => 'app_ipad_url',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataAppIpadUrl->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataAppIpadUrl->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);
        
        // Google Play
        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataAppGooglePlayName */
        $twitterCardMetadataAppGooglePlayName = $twitterCardMetadata->componentOptions()->create([
            'name' => 'App Google Play Name',
            'variable_name' => 'app_google_play_name',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataAppGooglePlayName->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataAppGooglePlayName->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataAppGooglePlayID */
        $twitterCardMetadataAppGooglePlayID = $twitterCardMetadata->componentOptions()->create([
            'name' => 'App Google Play ID',
            'variable_name' => 'app_google_play_id',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataAppGooglePlayID->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataAppGooglePlayID->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataAppGooglePlayUrl */
        $twitterCardMetadataAppGooglePlayUrl = $twitterCardMetadata->componentOptions()->create([
            'name' => 'App Google Play Url',
            'variable_name' => 'app_google_play_url',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataAppGooglePlayUrl->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataAppGooglePlayUrl->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        // Player
        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataPlayer */
        $twitterCardMetadataPlayer = $twitterCardMetadata->componentOptions()->create([
            'name' => 'Player',
            'variable_name' => 'player',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataPlayer->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataPlayer->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataPlayerWidth */
        $twitterCardMetadataPlayerWidth = $twitterCardMetadata->componentOptions()->create([
            'name' => 'Player Width',
            'variable_name' => 'player_width',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataPlayerWidth->upsertOptionValue(OptionValueConstants::INTEGER);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataPlayerHeight */
        $twitterCardMetadataPlayerHeight = $twitterCardMetadata->componentOptions()->create([
            'name' => 'Player Height',
            'variable_name' => 'player_height',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataPlayerHeight->upsertOptionValue(OptionValueConstants::INTEGER);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataPlayerStream */
        $twitterCardMetadataPlayerStream = $twitterCardMetadata->componentOptions()->create([
            'name' => 'Player Stream',
            'variable_name' => 'player_stream',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataPlayerStream->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataPlayerStream->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        /** @var \App\Api\Models\ComponentOption $twitterCardMetadataPlayerStreamContentType */
        $twitterCardMetadataPlayerStreamContentType = $twitterCardMetadata->componentOptions()->create([
            'name' => 'Player Stream Content Type',
            'variable_name' => 'player_stream_content_type',
            'is_required' => false,
            'is_active' => true
        ]);
        $twitterCardMetadataPlayerStreamContentType->upsertOptionValue(OptionValueConstants::STRING, '');
        $twitterCardMetadataPlayerStreamContentType->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => ''
        ]);

        //Favicon
        /** @var Component $favicon */
        $favicon = Component::create([
            'name' => 'Favicon',
            'variable_name' => 'favicon'
        ]);
        /** @var \App\Api\Models\ComponentOption $faviconIcon */
        $faviconIcon = $favicon->componentOptions()->create([
            'name' => 'Icon',
            'variable_name' => 'icon',
            'is_required' => true,
            'is_active' => true
        ]);
        $faviconIcon->upsertOptionValue(OptionValueConstants::STRING, '');
        $faviconIcon->upsertOptionElementType(OptionElementTypeConstants::FILE_UPLOAD, [
            'selectedOption' => '.ico,.png,.PNG,.ICO,.svg',
            'targetFileExtensions' => '.ico,.png,.ICO,.PNG,.svg',
            'imageSizeOption' => [
                'width' => '',
                'height' => '',
                'ratio' => '',
                'size' => ''
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $faviconSizes */
        $faviconSizes = $favicon->componentOptions()->create([
            'name' => 'Sizes',
            'variable_name' => 'sizes',
            'is_required' => false,
            'is_active' => true
        ]);
        $faviconSizes->upsertOptionValue(OptionValueConstants::STRING, '');
        $faviconSizes->upsertOptionElementType(OptionElementTypeConstants::DROPDOWN, [
            [
                'text' => '16x16px',
                'value' => '16x16'
            ],
            [
                'text' => '32x32px',
                'value' => '32x32'
            ],
            [
                'text' => '96x96px',
                'value' => '96x96'
            ]
        ]);

        //Favicon Group
        /** @var Component $faviconGroup */
        $faviconGroup = Component::create([
            'name' => 'Favicon Group',
            'variable_name' => 'favicon_group'
        ]);
        /** @var \App\Api\Models\ComponentOption $faviconGroupDefaultIcon */
        $faviconGroupDefaultIcon = $faviconGroup->componentOptions()->create([
            'name' => 'Default Icon',
            'variable_name' => 'default_icon',
            'is_required' => true,
            'is_active' => true
        ]);
        $faviconGroupDefaultIcon->upsertOptionValue(OptionValueConstants::STRING, '');
        $faviconGroupDefaultIcon->upsertOptionElementType(OptionElementTypeConstants::FILE_UPLOAD, [
            'selectedOption' => '.ico,.png,.PNG,.ICO,.svg',
            'targetFileExtensions' => '.ico,.png,.ICO,.PNG,.svg',
            'imageSizeOption' => [
                'width' => '',
                'height' => '',
                'ratio' => '',
                'size' => ''
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $faviconGroupIcons */
        $faviconGroupIcons = $faviconGroup->componentOptions()->create([
            'name' => 'Icons',
            'variable_name' => 'icons',
            'is_required' => false,
            'is_active' => true
        ]);
        $faviconGroupIcons->upsertOptionValue(OptionValueConstants::STRING, '');
        $faviconGroupIcons->upsertOptionElementType(OptionElementTypeConstants::CONTROL_LIST, [
            'selectedControlId' => $favicon->getKey(),
            'selectedControlName' => $favicon->name,
            'propStructure' => [
                [
                    'prop_id' => $faviconIcon->getKey(),
                    'name' => $faviconIcon->name,
                    'variable_name' => $faviconIcon->variable_name,
                    'element_type' => OptionElementTypeConstants::FILE_UPLOAD,
                    'element_value' => json_encode([
                        'selectedOption' => '.ico,.png,.PNG,.ICO,.svg',
                        'targetFileExtensions' => '.ico,.png,.ICO,.PNG,.svg',
                        'imageSizeOption' => [
                            'width' => '',
                            'height' => '',
                            'ratio' => '',
                            'size' => ''
                        ]
                    ]),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $faviconIcon->is_required
                ],
                [
                    'prop_id' => $faviconSizes->getKey(),
                    'name' => $faviconSizes->name,
                    'variable_name' => $faviconSizes->variable_name,
                    'element_type' => OptionElementTypeConstants::DROPDOWN,
                    'element_value' => json_encode([
                        [
                            'text' => '16x16px',
                            'value' => '16x16'
                        ],
                        [
                            'text' => '32x32px',
                            'value' => '32x32'
                        ],
                        [
                            'text' => '96x96px',
                            'value' => '96x96'
                        ]
                    ]),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $faviconSizes->is_required
                ]
            ]
        ]);

        //AppleTouchIcon
        /** @var Component $appleTouchIcon */
        $appleTouchIcon = Component::create([
            'name' => 'Apple Touch Icon',
            'variable_name' => 'apple_touch_icon'
        ]);
        /** @var \App\Api\Models\ComponentOption $appleTouchIconIcon */
        $appleTouchIconIcon = $appleTouchIcon->componentOptions()->create([
            'name' => 'Icon',
            'variable_name' => 'icon',
            'is_required' => true,
            'is_active' => true
        ]);
        $appleTouchIconIcon->upsertOptionValue(OptionValueConstants::STRING, '');
        $appleTouchIconIcon->upsertOptionElementType(OptionElementTypeConstants::FILE_UPLOAD, [
            'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg,.ico,.ICO',
            'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg,.ico,.ICO',
            'imageSizeOption' => [
                'width' => '',
                'height' => '',
                'ratio' => '',
                'size' => ''
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $appleTouchIconSizes */
        $appleTouchIconSizes = $appleTouchIcon->componentOptions()->create([
            'name' => 'Sizes',
            'variable_name' => 'sizes',
            'is_required' => true,
            'is_active' => true
        ]);
        $appleTouchIconSizes->upsertOptionValue(OptionValueConstants::STRING, '57x57');
        $appleTouchIconSizes->upsertOptionElementType(OptionElementTypeConstants::DROPDOWN, [
            [
                'text' => '57x57px',
                'value' => '57x57'
            ],
            [
                'text' => '60x60px',
                'value' => '60x60'
            ],
            [
                'text' => '72x72px',
                'value' => '72x72'
            ],
            [
                'text' => '76x76px',
                'value' => '76x76'
            ],
            [
                'text' => '114x114px',
                'value' => '114x114'
            ],
            [
                'text' => '120x120px',
                'value' => '120x120'
            ],
            [
                'text' => '144x144px',
                'value' => '144x144'
            ],
            [
                'text' => '120x120px',
                'value' => '120x120'
            ],
            [
                'text' => '152x152px',
                'value' => '152x162'
            ],
            [
                'text' => '180x180px',
                'value' => '180x180'
            ]
        ]);

        /** @var Component $appleTouchIconGroup */
        $appleTouchIconGroup = Component::create([
            'name' => 'Apple Touch Icon Group',
            'variable_name' => 'apple_touch_icon_group'
        ]);

        /** @var \App\Api\Models\ComponentOption $appleTouchIconGroupIcons */
        $appleTouchIconGroupIcons = $appleTouchIconGroup->componentOptions()->create([
            'name' => 'Icons',
            'variable_name' => 'icons',
            'is_required' => true,
            'is_active' => true
        ]);
        $appleTouchIconGroupIcons->upsertOptionValue(OptionValueConstants::STRING, '');
        $appleTouchIconGroupIcons->upsertOptionElementType(OptionElementTypeConstants::CONTROL_LIST, [
            'selectedControlId' => $appleTouchIcon->getKey(),
            'selectedControlName' => $appleTouchIcon->name,
            'propStructure' => [
                [
                    'prop_id' => $appleTouchIconIcon->getKey(),
                    'name' => $appleTouchIconIcon->name,
                    'variable_name' => $appleTouchIconIcon->variable_name,
                    'element_type' => OptionElementTypeConstants::FILE_UPLOAD,
                    'element_value' => json_encode([
                        'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg,.ico,.ICO',
                        'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg,.ico,.ICO',
                        'imageSizeOption' => [
                            'width' => '',
                            'height' => '',
                            'ratio' => '',
                            'size' => ''
                        ]
                    ]),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $appleTouchIconIcon->is_required
                ],
                [
                    'prop_id' => $appleTouchIconSizes->getKey(),
                    'name' => $appleTouchIconSizes->name,
                    'variable_name' => $appleTouchIconSizes->variable_name,
                    'element_type' => OptionElementTypeConstants::DROPDOWN,
                    'element_value' => json_encode([
                        [
                            'text' => '57x57px',
                            'value' => '57x57'
                        ],
                        [
                            'text' => '60x60px',
                            'value' => '60x60'
                        ],
                        [
                            'text' => '72x72px',
                            'value' => '72x72'
                        ],
                        [
                            'text' => '76x76px',
                            'value' => '76x76'
                        ],
                        [
                            'text' => '114x114px',
                            'value' => '114x114'
                        ],
                        [
                            'text' => '120x120px',
                            'value' => '120x120'
                        ],
                        [
                            'text' => '144x144px',
                            'value' => '144x144'
                        ],
                        [
                            'text' => '120x120px',
                            'value' => '120x120'
                        ],
                        [
                            'text' => '152x152px',
                            'value' => '152x152'
                        ],
                        [
                            'text' => '180x180px',
                            'value' => '180x180'
                        ]
                    ]),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $appleTouchIconSizes->is_required
                ]
            ]
        ]);

        // MS Application Icon
        /** @var Component $msApplicationIcon */
        $msApplicationIcon = Component::create([
            'name' => 'MS Application Icon',
            'variable_name' => 'ms_application_icon'
        ]);
        /** @var \App\Api\Models\ComponentOption $msApplicationIconIcon */
        $msApplicationIconIcon = $msApplicationIcon->componentOptions()->create([
            'name' => 'Icon',
            'variable_name' => 'icon',
            'is_required' => true,
            'is_active' => true
        ]);
        $msApplicationIconIcon->upsertOptionValue(OptionValueConstants::STRING, '');
        $msApplicationIconIcon->upsertOptionElementType(OptionElementTypeConstants::FILE_UPLOAD, [
            'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg,.ico,.ICO',
            'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg,.ico,.ICO',
            'imageSizeOption' => [
                'width' => '',
                'height' => '',
                'ratio' => '',
                'size' => ''
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $msApplicationName */
        $msApplicationName = $msApplicationIcon->componentOptions()->create([
            'name' => 'Name',
            'variable_name' => 'name',
            'is_required' => true,
            'is_active' => true
        ]);
        $msApplicationName->upsertOptionValue(OptionValueConstants::STRING, 'msapplication-square70x70logo');
        $msApplicationName->upsertOptionElementType(OptionElementTypeConstants::DROPDOWN, [
            [
                'text' => 'Square 70x70px',
                'value' => 'msapplication-square70x70logo'
            ],
            [
                'text' => 'Square 150x150px',
                'value' => 'msapplication-square150x150logo'
            ],
            [
                'text' => 'Wide 310x150px',
                'value' => 'msapplication-wide310x150logo'
            ],
            [
                'text' => 'Square 310x310px',
                'value' => 'msapplication-square310x310logo'
            ]
        ]);

        /** @var Component $msApplicationIconGroup */
        $msApplicationIconGroup = Component::create([
            'name' => 'MS Application Icon Group',
            'variable_name' => 'ms_application_icon_group'
        ]);

        /** @var \App\Api\Models\ComponentOption $msApplicationIconGroupIcons */
        $msApplicationIconGroupIcons = $msApplicationIconGroup->componentOptions()->create([
            'name' => 'Icons',
            'variable_name' => 'icons',
            'is_required' => true,
            'is_active' => true
        ]);
        $msApplicationIconGroupIcons->upsertOptionValue(OptionValueConstants::STRING, '');
        $msApplicationIconGroupIcons->upsertOptionElementType(OptionElementTypeConstants::CONTROL_LIST, [
            'selectedControlId' => $msApplicationIcon->getKey(),
            'selectedControlName' => $msApplicationIcon->name,
            'propStructure' => [
                [
                    'prop_id' => $msApplicationIconIcon->getKey(),
                    'name' => $msApplicationIconIcon->name,
                    'variable_name' => $msApplicationIconIcon->variable_name,
                    'element_type' => OptionElementTypeConstants::FILE_UPLOAD,
                    'element_value' => json_encode([
                        'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg,.ico,.ICO',
                        'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg,.ico,.ICO',
                        'imageSizeOption' => [
                            'width' => '',
                            'height' => '',
                            'ratio' => '',
                            'size' => ''
                        ]
                    ]),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $msApplicationIconIcon->is_required
                ],
                [
                    'prop_id' => $msApplicationName->getKey(),
                    'name' => $msApplicationName->name,
                    'variable_name' => $msApplicationName->variable_name,
                    'element_type' => OptionElementTypeConstants::DROPDOWN,
                    'element_value' => json_encode([
                        [
                            'text' => 'Square 70x70px',
                            'value' => 'msapplication-square70x70logo'
                        ],
                        [
                            'text' => 'Square 150x150px',
                            'value' => 'msapplication-square150x150logo'
                        ],
                        [
                            'text' => 'Wide 310x150px',
                            'value' => 'msapplication-wide310x150logo'
                        ],
                        [
                            'text' => 'Square 310x310px',
                            'value' => 'msapplication-square310x310logo'
                        ]
                    ]),
                    'option_type' => OptionValueConstants::STRING,
                    'option_value' => '',
                    'is_required' => $msApplicationName->is_required
                ]
            ]
        ]);

        // Theme-color metadata
        /** @var Component $themeColorMetadata */
        $themeColorMetadata = Component::create([
            'name' => 'Theme-color Metadata',
            'variable_name' => 'theme_color_metadata'
        ]);
        /** @var \App\Api\Models\ComponentOption $themeColorMetadataColor */
        $themeColorMetadataColor = $themeColorMetadata->componentOptions()->create([
            'name' => 'Color',
            'variable_name' => 'color',
            'is_required' => true,
            'is_active' => true
        ]);
        $themeColorMetadataColor->upsertOptionValue(OptionValueConstants::STRING, '000000');
        $themeColorMetadataColor->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => 'colorpicker'
        ]);

        // MS Tile Metadata
        /** @var Component $msTileMetadata */
        $msTileMetadata = Component::create([
            'name' => 'MS Tile Metadata',
            'variable_name' => 'ms_tile_metadata'
        ]);
        /** @var \App\Api\Models\ComponentOption $msTileMetadataImage */
        $msTileMetadataImage = $msTileMetadata->componentOptions()->create([
            'name' => 'Image',
            'variable_name' => 'image',
            'is_required' => true,
            'is_active' => true
        ]);
        $msTileMetadataImage->upsertOptionValue(OptionValueConstants::STRING, '');
        $msTileMetadataImage->upsertOptionElementType(OptionElementTypeConstants::FILE_UPLOAD, [
            'selectedOption' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'targetFileExtensions' => '.jpg,.jpeg,.png,.gif,.JPG,.JPEG,.PNG,.GIF,.svg',
            'imageSizeOption' => [
                'width' => '',
                'height' => '',
                'ratio' => '',
                'size' => ''
            ]
        ]);

        /** @var \App\Api\Models\ComponentOption $msTileMetadataColor */
        $msTileMetadataColor = $msTileMetadata->componentOptions()->create([
            'name' => 'Color',
            'variable_name' => 'color',
            'is_required' => true,
            'is_active' => true
        ]);
        $msTileMetadataColor->upsertOptionValue(OptionValueConstants::STRING, '000000');
        $msTileMetadataColor->upsertOptionElementType(OptionElementTypeConstants::TEXTBOX, [
            'helper' => 'colorpicker'
        ]);
    }
}
