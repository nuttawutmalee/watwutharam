<?php
namespace App\Api\Constants;

class ErrorMessageConstants extends EnumType
{
    const INVALID_LOGIN = 'Invalid login';
    const INVALID_LOGOUT = 'Could not logout, please try again later';
    const PERMISSION_DENIED = 'Permission denied';

    const DUPLICATE_VARIABLE_NAME = 'Variable name is not unique';
    const DUPLICATE_FRIENDLY_URL = 'Friendly Url is not unique';
    const INVALID_FRIENDLY_URL = 'Friendly Url is invalid';

    const DUPLICATE_SOURCE_AND_DESTINATION_URL = 'Combination of the source url and destination url is already existed.';

    const FILE_UPLOAD_NOT_ALLOWED = 'File uploading is not allowed';
    const FILE_UPLOAD_IS_REQUIRED = 'File is required, please upload a file';
    const FILE_PATH_INVALID = 'File path is invalid';
    const FILE_PATH_IS_REQUIRED = 'File path is required';
    const FILE_PATH_IS_RESERVED = 'File path is reserved';
    const FILE_SIZE_IS_LARGER_THAN_RECOMMENDED = 'File size is larger than recommended';
    const FILE_NOT_FOUND = 'File not found';
    const FILE_ALREADY_EXISTS = 'File already exists';

    const PARENT_NOT_FOUND = 'Parent model not found';
    const FROM_DIFFERENT_SITE = 'This item is from a different site';
    const ITEM_OPTION_NOT_FOUND = 'Item option not found';
    const SITE_NOT_FOUND = 'Site not found';
    const PAGE_NOT_FOUND = 'Page not found';
    const GLOBAL_ITEM_NOT_FOUND = 'Global item not found';
    const CLASS_NOT_FOUND = 'Class not found';
    const RELATIONSHIP_NOT_FOUND = 'Relationship not found';
    const METHOD_NOT_FOUND = 'Method not found';
    const SITE_LANGUAGE_NOT_FOUND = 'Cannot find the language in any sites';
    const NOT_CONTROL_LIST_ELEMENT_TYPE = 'This item is not a control list';

    const WRONG_MODEL = 'Wrong input model';
    const INACTIVE_MODEL = 'Model or its predecessors are inactive';
    const CANNOT_ATTACH_TO_ITSELF = 'You cannot attach itself as a parent';
    const LOOP_RELATIONSHIP_DETECTED = 'Loop relationship detected';

    const INVALID_OPTION_TYPE = 'Invalid option type';
    const INVALID_DATE_STRING_FORMAT = 'Invalid date string format';

    const UNRECOVERABLE_ITEM = 'Unrecoverable item';

    const PREVIEW_DOMAIN_NOT_FOUND = 'Site domain name for preview in cms config file not found';

    const SPAM_BOT_DETECTED = 'Spam bot detected';
    const OVERRIDE_SENDER_EMAIL_CANNOT_BE_MULTIPLE = 'Override sender email cannot be multiple';

    const FORM_TOKEN_IS_REQUIRED = 'Form token is required';
    const FORM_TOKEN_IS_MISSING = 'Form token is missing';
    const FORM_TOKEN_MISMATCHED = 'Form token mismatched';
    const FORM_TOKEN_IS_NOT_ENCRYPTED = ' Form token is not encrypted';

    const INVALID_UNLOCK_DATABASE_CODE = 'Invalid unlock database code';
    const CODE_NOT_FOUND = 'Code not found';
    const INVALID_CMS_HEADER = 'Invalid cms application header';

    public function getFields()
    {
        return [
            'INVALID_LOGIN',
            'INVALID_LOGOUT',
            'PERMISSION_DENIED',

            'DUPLICATE_VARIABLE_NAME',
            'DUPLICATE_FRIENDLY_URL',
            'INVALID_FRIENDLY_URL',

            'DUPLICATE_SOURCE_AND_DESTINATION_URL',

            'FILE_UPLOAD_NOT_ALLOWED',
            'FILE_UPLOAD_IS_REQUIRED',
            'FILE_PATH_INVALID',
            'FILE_PATH_IS_REQUIRED',
            'FILE_PATH_IS_RESERVED',
            'FILE_SIZE_IS_LARGER_THAN_RECOMMENDED',
            'FILE_NOT_FOUND',
            'FILE_ALREADY_EXISTS',

            'PARENT_NOT_FOUND',
            'FROM_DIFFERENT_SITE',
            'ITEM_OPTION_NOT_FOUND',
            'SITE_NOT_FOUND',
            'PAGE_NOT_FOUND',
            'GLOBAL_ITEM_NOT_FOUND',
            'CLASS_NOT_FOUND',
            'RELATIONSHIP_NOT_FOUND',
            'METHOD_NOT_FOUND',
            'SITE_LANGUAGE_NOT_FOUND',
            'NOT_CONTROL_LIST_ELEMENT_TYPE',

            'WRONG_MODEL',
            'INACTIVE_MODEL',
            'CANNOT_ATTACH_TO_ITSELF',
            'LOOP_RELATIONSHIP_DETECTED',

            'INVALID_OPTION_TYPE',
            'INVALID_DATE_STRING_FORMAT',

            'UNRECOVERABLE_ITEM',

            'PREVIEW_DOMAIN_NOT_FOUND',

            'SPAM_BOT_DETECTED',
            'OVERRIDE_SENDER_EMAIL_CANNOT_BE_MULTIPLE',

            'FORM_TOKEN_IS_REQUIRED',
            'FORM_TOKEN_IS_MISSING',
            'FORM_TOKEN_MISMATCHED',
            'FORM_TOKEN_IS_NOT_ENCRYPTED',

            'INVALID_UNLOCK_DATABASE_CODE',
            'CODE_NOT_FOUND',

            'INVALID_CMS_HEADER',
        ];
    }
}