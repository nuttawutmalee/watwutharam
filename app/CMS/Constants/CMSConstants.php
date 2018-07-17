<?php
namespace App\CMS\Constants;

class CMSConstants extends EnumType
{
    const SITE = 'site';
    const SITE_MAIN_LANGUAGE = 'site_main_language';
    const SITE_LANGUAGES = 'site_languages';
    const TEMPLATE = 'template';
    const PAGE = 'page';
    const GLOBAL_ITEMS = 'global_items';

    //Type
    const GLOBAL_ITEM = 'GlobalItem';
    const PAGE_ITEM = 'PageItem';
    const TEMPLATE_ITEM = 'TemplateItem';

    //Language
    const LANGUAGE_NAME = 'language_name';
    const LANGUAGE_CODE = 'language_code';
    const CURRENT_LANGUAGE_CODE = 'current_language_code';
    const CODE = 'code';
    const CURRENT_LOCALE = 'current_locale';
    const CURRENT_HREFLANG = 'current_hreflang';

    //API
    const CMS_API = 'CMS_API';
    const CMS_PAGE_PREVIEW_ACTIVE = 'cms_page_preview_active';
    const FORM_TOKEN_HEADER = 'X-CMS-Form-Token';
    const FORM_TOKEN_FIELD = '_cms_form_token';
    const CMS_APPLICATION_NAME_HEADER = 'X-CMS-Application-Name';
    const CMS_APPLICATION_NAME_FIELD = '_cms_application_name';
    const HONEY_POT_FIELD = '_honey_pot';
    const FORM_OVERRIDE_SENDER_EMAIL = '_form_override_sender_email';
    const FORM_OVERRIDE_RECIPIENT_EMAILS = '_form_override_recipient_emails';
    const FORM_OVERRIDE_CC_EMAILS = '_form_override_cc_emails';
    const FORM_OVERRIDE_BCC_EMAILS = '_form_override_bcc_emails';

    //Custom
    const CATEGORIES_KEY = '_categories';
    const OPTION_TYPE = '_type';
    const ELEMENT_TYPE = '_element_type';
    const ELEMENT_VALUE = '_element_value';
    const PARENT = '_parent';
    const PARENTS = '_parents';
    const CHILDREN = '_children';
    const DISPLAY_ORDER = '_display_order';
    const TEMPLATE_VIEW_PATH = 'templates';
    const VARIABLE_NAME = '_variable_name';
    const PAGE_DATA = '_page_data';
    const TEMPLATE_DATA = '_template_data';
    const CREATED_DATE = '_created_at';
    const UPDATED_DATE = '_updated_at';
    const TEMPLATE_NAME = '_template';
    const QUERY = '_query';
    const QUERY_PAGINATION_VIEW_PATH = 'paginations';

    //Template render
    const TEMPLATE_PATH = 'templatePath';
    const TEMPLATE_ITEM_KEY = 'templateItem';
    const PAGE_ITEM_KEY = 'pageItem';
    const RENDER_DATA = 'data';

    //FILES
    const DIRECTORY = 'DIRECTORY';
    const FILE = 'FILE';

    const SITEMAPS_FOLDER = 'sitemaps';

    const API_FORM_TOKEN_HEADER = 'X-CMS-Form-Token';
    const API_FORM_TOKEN_FIELD = '_cms_form_token';

    const CURRENT_BASE_CURRENCY = 'current_base_currency';
    const GEOIP_ENABLED = 'geoip_enabled';

    const PAGINATOR_DIRECTORY = 'paginations';
    const URL_LIST = 'url_list';

    const ADDITIONAL_SCRIPTS = 'additional_scripts';
    const ADDITIONAL_SCRIPTS_HEAD = 'head';
    const ADDITIONAL_SCRIPTS_BODY_TOP = 'body_top';
    const ADDITIONAL_SCRIPTS_BODY_BOTTOM = 'body_bottom';

    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    const ORDER_BY_UPDATED_AT = 'updated_at';
    const ORDER_BY_CREATED_AT = 'created_at';
    const ORDER_BY_PUBLISHED_AT = 'published_at';
    const NEXT_DIRECTION = 'next';
    const PREVIOUS_DIRECTION = 'previous';

    public function getFields()
    {
        return [
            'SITE',
            'SITE_MAIN_LANGUAGE',
            'SITE_LANGUAGES',
            'TEMPLATE',
            'PAGE',
            'GLOBAL_ITEMS',

            'GLOBAL_ITEM',
            'PAGE_ITEM',
            'TEMPLATE_ITEM',

            'LANGUAGE_NAME',
            'LANGUAGE_CODE',
            'CURRENT_LANGUAGE_CODE',
            'CODE',
            'CURRENT_LOCALE',
            'CURRENT_HREFLANG',

            'CMS_API',
            'CMS_PAGE_PREVIEW_ACTIVE',
            'FORM_TOKEN_HEADER',
            'FORM_TOKEN_FIELD',
            'CMS_APPLICATION_NAME_HEADER',
            'CMS_APPLICATION_NAME_FIELD',
            'HONEY_POT_FIELD',
            'FORM_OVERRIDE_SENDER_EMAIL',
            'FORM_OVERRIDE_RECIPIENT_EMAILS',
            'FORM_OVERRIDE_CC_EMAILS',
            'FORM_OVERRIDE_BCC_EMAILS',

            'CATEGORIES_KEY',
            'OPTION_TYPE',
            'ELEMENT_TYPE',
            'ELEMENT_VALUE',
            'PARENT',
            'PARENTS',
            'DISPLAY_ORDER',
            'TEMPLATE_VIEW_PATH',
            'VARIABLE_NAME',
            'PAGE_DATA',
            'TEMPLATE_DATA',
            'CREATED_DATE',
            'UPDATED_DATE',
            'TEMPLATE_NAME',
            'QUERY',
            'QUERY_PAGINATION_VIEW_PATH',

            'TEMPLATE_ITEM_KEY',
            'PAGE_ITEM_KEY',
            'TEMPLATE_PATH',
            'RENDER_DATA',

            'DIRECTORY',
            'FILE',

            'SITEMAPS_FOLDER',

            'API_FORM_TOKEN_HEADER',
            'API_FORM_TOKEN_FIELD',

            'CURRENT_BASE_CURRENCY',
            'GEOIP_ENABLED',

            'PAGINATOR_DIRECTORY',
            'URL_LIST',

            'ADDITIONAL_SCRIPTS',
            'ADDITIONAL_SCRIPTS_HEAD',
            'ADDITIONAL_SCRIPTS_BODY_TOP',
            'ADDITIONAL_SCRIPTS_BODY_BOTTOM',

            'ORDER_ASC',
            'ORDER_DESC',
            'ORDER_BY_UPDATED_AT',
            'ORDER_BY_CREATED_AT',
            'ORDER_BY_PUBLISHED_AT',
            'NEXT_DIRECTION',
            'PREVIOUS_DIRECTION'
        ];
    }
}