<?php
namespace App\Api\Constants;

class HelperConstants extends EnumType
{
    const HELPER_ID = '_id';
    const HELPER_OPTION_TYPE = '_type';
    const HELPER_ELEMENT_TYPE = '_element_type';
    const HELPER_ELEMENT_VALUE = '_element_value';
    const HELPER_CATEGORIES = '_categories';
    const HELPER_PARENT = '_parent';
    const HELPER_PARENTS = '_parents';
    const HELPER_CHILDREN = '_children';
    const HELPER_DISPLAY_ORDER = '_display_order';
    const HELPER_VARIABLE_NAME = '_variable_name';
    const HELPER_PAGE_DATA = '_page_data';
    const HELPER_TEMPLATE_DATA = '_template_data';
    const HELPER_CREATED_AT = '_created_at';
    const HELPER_UPDATED_AT = '_updated_at';
    const HELPER_PUBLISHED_AT = '_published_at';
    const HELPER_TEMPLATE = '_template';
    const HELPER_QUERY = '_query';

    const LANGUAGE_CODE = '_lang';
    const DOMAIN_NAME = '_domain_name';

    const APPLICATION_NAME = 'application_name';
    const UPLOADS_FOLDER = 'uploads';
    const UPLOADS_FOLDER_TESTING = 'uploads-test';

    const PREVIOUS_DIRECTION = 'previous';
    const NEXT_DIRECTION = 'next';

    public function getFields()
    {
        return [
            'HELPER_OPTION_TYPE',
            'HELPER_ELEMENT_TYPE',
            'HELPER_ELEMENT_VALUE',
            'HELPER_CATEGORIES',
            'HELPER_PARENT',
            'HELPER_PARENTS',
            'HELPER_CHILDREN',
            'HELPER_DISPLAY_ORDER',
            'HELPER_VARIABLE_NAME',
            'HELPER_PAGE_DATA',
            'HELPER_TEMPLATE_DATA',
            'HELPER_CREATED_AT',
            'HELPER_UPDATED_AT',
            'HELPER_PUBLISHED_AT',
            'HELPER_TEMPLATE',
            'HELPER_QUERY',

            'LANGUAGE_CODE',
            'DOMAIN_NAME',

            'APPLICATION_NAME',
            'UPLOADS_FOLDER',
            'UPLOADS_FOLDER_TESTING',

            'PREVIOUS_DIRECTION',
            'NEXT_DIRECTION'
        ];
    }
}