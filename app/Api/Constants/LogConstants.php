<?php
namespace App\Api\Constants;

class LogConstants extends EnumType
{
    const SYSTEM = 'SYSTEM';
    const RECOVERABLE_ID = '_RECOVERABLE_ID';
    const FORM_SUBMIT = 'FORM_SUBMIT';
    const DATABASE_BACKUP_DATE_FORMAT = 'Y-m-d-h-i-s-A';

    /* USER */
    const USER_LOGIN = 'USER_LOGIN';
    const USER_CREATED = 'USER_CREATED';
    const USER_BEFORE_UPDATED = 'USER_BEFORE_UPDATED';
    const USER_UPDATED = 'USER_UPDATED';
    const USER_SAVED = 'USER_SAVED';
    const USER_BEFORE_DELETED = 'USER_BEFORE_DELETED';
    
    /* CMS ROLE */
    const CMS_ROLE_CREATED = 'CMS_ROLE_CREATED';
    const CMS_ROLE_BEFORE_UPDATED = 'CMS_ROLE_BEFORE_UPDATED';
    const CMS_ROLE_UPDATED = 'CMS_ROLE_UPDATED';
    const CMS_ROLE_SAVED = 'CMS_ROLE_SAVED';
    const CMS_ROLE_BEFORE_DELETED = 'CMS_ROLE_BEFORE_DELETED';
    
    /* SITE */
    const SITE_CREATED = 'SITE_CREATED';
    const SITE_BEFORE_UPDATED = 'SITE_BEFORE_UPDATED';
    const SITE_UPDATED = 'SITE_UPDATED';
    const SITE_SAVED = 'SITE_SAVED';
    const SITE_BEFORE_DELETED = 'SITE_BEFORE_DELETED';
    
    /* COMPONENT */
    const COMPONENT_CREATED = 'COMPONENT_CREATED';
    const COMPONENT_BEFORE_UPDATED = 'COMPONENT_BEFORE_UPDATED';
    const COMPONENT_UPDATED = 'COMPONENT_UPDATED';
    const COMPONENT_SAVED = 'COMPONENT_SAVED';
    const COMPONENT_BEFORE_DELETED = 'COMPONENT_BEFORE_DELETED';
    const COMPONENT_CASCADE_UPDATE_INHERITANCES = 'COMPONENT_CASCADE_UPDATE_INHERITANCES';

    /* COMPONENT OPTION */
    const COMPONENT_OPTION_CREATED = 'COMPONENT_OPTION_CREATED';
    const COMPONENT_OPTION_BEFORE_UPDATED = 'COMPONENT_OPTION_BEFORE_UPDATED';
    const COMPONENT_OPTION_UPDATED = 'COMPONENT_OPTION_UPDATED';
    const COMPONENT_OPTION_SAVED = 'COMPONENT_OPTION_SAVED';
    const COMPONENT_OPTION_BEFORE_DELETED = 'COMPONENT_OPTION_BEFORE_DELETED';
    
    /* COMPONENT OPTION ITEM DATE */
    const COMPONENT_OPTION_DATE_CREATED = 'COMPONENT_OPTION_DATE_CREATED';
    const COMPONENT_OPTION_DATE_BEFORE_UPDATED = 'COMPONENT_OPTION_DATE_BEFORE_UPDATED';
    const COMPONENT_OPTION_DATE_UPDATED = 'COMPONENT_OPTION_DATE_UPDATED';
    const COMPONENT_OPTION_DATE_SAVED = 'COMPONENT_OPTION_DATE_SAVED';
    const COMPONENT_OPTION_DATE_BEFORE_DELETED = 'COMPONENT_OPTION_DATE_BEFORE_DELETED';
    
    /* COMPONENT OPTION ITEM DECIMAL */
    const COMPONENT_OPTION_DECIMAL_CREATED = 'COMPONENT_OPTION_DECIMAL_CREATED';
    const COMPONENT_OPTION_DECIMAL_BEFORE_UPDATED = 'COMPONENT_OPTION_DECIMAL_BEFORE_UPDATED';
    const COMPONENT_OPTION_DECIMAL_UPDATED = 'COMPONENT_OPTION_DECIMAL_UPDATED';
    const COMPONENT_OPTION_DECIMAL_SAVED = 'COMPONENT_OPTION_DECIMAL_SAVED';
    const COMPONENT_OPTION_DECIMAL_BEFORE_DELETED = 'COMPONENT_OPTION_DECIMAL_BEFORE_DELETED';
    
    /* COMPONENT OPTION ITEM INTEGER */
    const COMPONENT_OPTION_INTEGER_CREATED = 'COMPONENT_OPTION_INTEGER_CREATED';
    const COMPONENT_OPTION_INTEGER_BEFORE_UPDATED = 'COMPONENT_OPTION_INTEGER_BEFORE_UPDATED';
    const COMPONENT_OPTION_INTEGER_UPDATED = 'COMPONENT_OPTION_INTEGER_UPDATED';
    const COMPONENT_OPTION_INTEGER_SAVED = 'COMPONENT_OPTION_INTEGER_SAVED';
    const COMPONENT_OPTION_INTEGER_BEFORE_DELETED = 'COMPONENT_OPTION_INTEGER_BEFORE_DELETED';
    
    /* COMPONENT OPTION ITEM STRING */
    const COMPONENT_OPTION_STRING_CREATED = 'COMPONENT_OPTION_STRING_CREATED';
    const COMPONENT_OPTION_STRING_BEFORE_UPDATED = 'COMPONENT_OPTION_STRING_BEFORE_UPDATED';
    const COMPONENT_OPTION_STRING_UPDATED = 'COMPONENT_OPTION_STRING_UPDATED';
    const COMPONENT_OPTION_STRING_SAVED = 'COMPONENT_OPTION_STRING_SAVED';
    const COMPONENT_OPTION_STRING_BEFORE_DELETED = 'COMPONENT_OPTION_STRING_BEFORE_DELETED';
    
    /* REDIRECT URL */
    const REDIRECT_URL_CREATED = 'REDIRECT_URL_CREATED';
    const REDIRECT_URL_BEFORE_UPDATED = 'REDIRECT_URL_BEFORE_UPDATED';
    const REDIRECT_URL_UPDATED = 'REDIRECT_URL_UPDATED';
    const REDIRECT_URL_SAVED = 'REDIRECT_URL_SAVED';
    const REDIRECT_URL_BEFORE_DELETED = 'REDIRECT_URL_BEFORE_DELETED';
    
    /* GLOBAL ITEM */
    const GLOBAL_ITEM_CREATED = 'GLOBAL_ITEM_CREATED';
    const GLOBAL_ITEM_BEFORE_UPDATED = 'GLOBAL_ITEM_BEFORE_UPDATED';
    const GLOBAL_ITEM_UPDATED = 'GLOBAL_ITEM_UPDATED';
    const GLOBAL_ITEM_SAVED = 'GLOBAL_ITEM_SAVED';
    const GLOBAL_ITEM_BEFORE_DELETED = 'GLOBAL_ITEM_BEFORE_DELETED';
    const GLOBAL_ITEM_DELETED = 'GLOBAL_ITEM_DELETED';
    
    /* GLOBAL ITEM OPTION */
    const GLOBAL_ITEM_OPTION_CREATED = 'GLOBAL_ITEM_OPTION_CREATED';
    const GLOBAL_ITEM_OPTION_BEFORE_UPDATED = 'GLOBAL_ITEM_OPTION_BEFORE_UPDATED';
    const GLOBAL_ITEM_OPTION_UPDATED = 'GLOBAL_ITEM_OPTION_UPDATED';
    const GLOBAL_ITEM_OPTION_SAVED = 'GLOBAL_ITEM_OPTION_SAVED';
    const GLOBAL_ITEM_OPTION_BEFORE_DELETED = 'GLOBAL_ITEM_OPTION_BEFORE_DELETED';

    /* GLOBAL_ITEM OPTION ITEM DATE */
    const GLOBAL_ITEM_OPTION_DATE_CREATED = 'GLOBAL_ITEM_OPTION_DATE_CREATED';
    const GLOBAL_ITEM_OPTION_DATE_BEFORE_UPDATED = 'GLOBAL_ITEM_OPTION_DATE_BEFORE_UPDATED';
    const GLOBAL_ITEM_OPTION_DATE_UPDATED = 'GLOBAL_ITEM_OPTION_DATE_UPDATED';
    const GLOBAL_ITEM_OPTION_DATE_SAVED = 'GLOBAL_ITEM_OPTION_DATE_SAVED';
    const GLOBAL_ITEM_OPTION_DATE_BEFORE_DELETED = 'GLOBAL_ITEM_OPTION_DATE_BEFORE_DELETED';

    /* GLOBAL_ITEM OPTION ITEM DECIMAL */
    const GLOBAL_ITEM_OPTION_DECIMAL_CREATED = 'GLOBAL_ITEM_OPTION_DECIMAL_CREATED';
    const GLOBAL_ITEM_OPTION_DECIMAL_BEFORE_UPDATED = 'GLOBAL_ITEM_OPTION_DECIMAL_BEFORE_UPDATED';
    const GLOBAL_ITEM_OPTION_DECIMAL_UPDATED = 'GLOBAL_ITEM_OPTION_DECIMAL_UPDATED';
    const GLOBAL_ITEM_OPTION_DECIMAL_SAVED = 'GLOBAL_ITEM_OPTION_DECIMAL_SAVED';
    const GLOBAL_ITEM_OPTION_DECIMAL_BEFORE_DELETED = 'GLOBAL_ITEM_OPTION_DECIMAL_BEFORE_DELETED';

    /* GLOBAL_ITEM OPTION ITEM INTEGER */
    const GLOBAL_ITEM_OPTION_INTEGER_CREATED = 'GLOBAL_ITEM_OPTION_INTEGER_CREATED';
    const GLOBAL_ITEM_OPTION_INTEGER_BEFORE_UPDATED = 'GLOBAL_ITEM_OPTION_INTEGER_BEFORE_UPDATED';
    const GLOBAL_ITEM_OPTION_INTEGER_UPDATED = 'GLOBAL_ITEM_OPTION_INTEGER_UPDATED';
    const GLOBAL_ITEM_OPTION_INTEGER_SAVED = 'GLOBAL_ITEM_OPTION_INTEGER_SAVED';
    const GLOBAL_ITEM_OPTION_INTEGER_BEFORE_DELETED = 'GLOBAL_ITEM_OPTION_INTEGER_BEFORE_DELETED';

    /* GLOBAL_ITEM OPTION ITEM STRING */
    const GLOBAL_ITEM_OPTION_STRING_CREATED = 'GLOBAL_ITEM_OPTION_STRING_CREATED';
    const GLOBAL_ITEM_OPTION_STRING_BEFORE_UPDATED = 'GLOBAL_ITEM_OPTION_STRING_BEFORE_UPDATED';
    const GLOBAL_ITEM_OPTION_STRING_UPDATED = 'GLOBAL_ITEM_OPTION_STRING_UPDATED';
    const GLOBAL_ITEM_OPTION_STRING_SAVED = 'GLOBAL_ITEM_OPTION_STRING_SAVED';
    const GLOBAL_ITEM_OPTION_STRING_BEFORE_DELETED = 'GLOBAL_ITEM_OPTION_STRING_BEFORE_DELETED';
    
    /* TEMPLATE */
    const TEMPLATE_CREATED = 'TEMPLATE_CREATED';
    const TEMPLATE_BEFORE_UPDATED = 'TEMPLATE_BEFORE_UPDATED';
    const TEMPLATE_UPDATED = 'TEMPLATE_UPDATED';
    const TEMPLATE_SAVED = 'TEMPLATE_SAVED';
    const TEMPLATE_BEFORE_DELETED = 'TEMPLATE_BEFORE_DELETED';
    
    /* TEMPLATE ITEM */
    const TEMPLATE_ITEM_CREATED = 'TEMPLATE_ITEM_CREATED';
    const TEMPLATE_ITEM_BEFORE_UPDATED = 'TEMPLATE_ITEM_BEFORE_UPDATED';
    const TEMPLATE_ITEM_UPDATED = 'TEMPLATE_ITEM_UPDATED';
    const TEMPLATE_ITEM_SAVED = 'TEMPLATE_ITEM_SAVED';
    const TEMPLATE_ITEM_BEFORE_DELETED = 'TEMPLATE_ITEM_BEFORE_DELETED';
    
    /* TEMPLATE ITEM OPTION */
    const TEMPLATE_ITEM_OPTION_CREATED = 'TEMPLATE_ITEM_OPTION_CREATED';
    const TEMPLATE_ITEM_OPTION_BEFORE_UPDATED = 'TEMPLATE_ITEM_OPTION_BEFORE_UPDATED';
    const TEMPLATE_ITEM_OPTION_UPDATED = 'TEMPLATE_ITEM_OPTION_UPDATED';
    const TEMPLATE_ITEM_OPTION_SAVED = 'TEMPLATE_ITEM_OPTION_SAVED';
    const TEMPLATE_ITEM_OPTION_BEFORE_DELETED = 'TEMPLATE_ITEM_OPTION_BEFORE_DELETED';

    /* TEMPLATE_ITEM OPTION ITEM DATE */
    const TEMPLATE_ITEM_OPTION_DATE_CREATED = 'TEMPLATE_ITEM_OPTION_DATE_CREATED';
    const TEMPLATE_ITEM_OPTION_DATE_BEFORE_UPDATED = 'TEMPLATE_ITEM_OPTION_DATE_BEFORE_UPDATED';
    const TEMPLATE_ITEM_OPTION_DATE_UPDATED = 'TEMPLATE_ITEM_OPTION_DATE_UPDATED';
    const TEMPLATE_ITEM_OPTION_DATE_SAVED = 'TEMPLATE_ITEM_OPTION_DATE_SAVED';
    const TEMPLATE_ITEM_OPTION_DATE_BEFORE_DELETED = 'TEMPLATE_ITEM_OPTION_DATE_BEFORE_DELETED';

    /* TEMPLATE_ITEM OPTION ITEM DECIMAL */
    const TEMPLATE_ITEM_OPTION_DECIMAL_CREATED = 'TEMPLATE_ITEM_OPTION_DECIMAL_CREATED';
    const TEMPLATE_ITEM_OPTION_DECIMAL_BEFORE_UPDATED = 'TEMPLATE_ITEM_OPTION_DECIMAL_BEFORE_UPDATED';
    const TEMPLATE_ITEM_OPTION_DECIMAL_UPDATED = 'TEMPLATE_ITEM_OPTION_DECIMAL_UPDATED';
    const TEMPLATE_ITEM_OPTION_DECIMAL_SAVED = 'TEMPLATE_ITEM_OPTION_DECIMAL_SAVED';
    const TEMPLATE_ITEM_OPTION_DECIMAL_BEFORE_DELETED = 'TEMPLATE_ITEM_OPTION_DECIMAL_BEFORE_DELETED';

    /* TEMPLATE_ITEM OPTION ITEM INTEGER */
    const TEMPLATE_ITEM_OPTION_INTEGER_CREATED = 'TEMPLATE_ITEM_OPTION_INTEGER_CREATED';
    const TEMPLATE_ITEM_OPTION_INTEGER_BEFORE_UPDATED = 'TEMPLATE_ITEM_OPTION_INTEGER_BEFORE_UPDATED';
    const TEMPLATE_ITEM_OPTION_INTEGER_UPDATED = 'TEMPLATE_ITEM_OPTION_INTEGER_UPDATED';
    const TEMPLATE_ITEM_OPTION_INTEGER_SAVED = 'TEMPLATE_ITEM_OPTION_INTEGER_SAVED';
    const TEMPLATE_ITEM_OPTION_INTEGER_BEFORE_DELETED = 'TEMPLATE_ITEM_OPTION_INTEGER_BEFORE_DELETED';

    /* TEMPLATE_ITEM OPTION ITEM STRING */
    const TEMPLATE_ITEM_OPTION_STRING_CREATED = 'TEMPLATE_ITEM_OPTION_STRING_CREATED';
    const TEMPLATE_ITEM_OPTION_STRING_BEFORE_UPDATED = 'TEMPLATE_ITEM_OPTION_STRING_BEFORE_UPDATED';
    const TEMPLATE_ITEM_OPTION_STRING_UPDATED = 'TEMPLATE_ITEM_OPTION_STRING_UPDATED';
    const TEMPLATE_ITEM_OPTION_STRING_SAVED = 'TEMPLATE_ITEM_OPTION_STRING_SAVED';
    const TEMPLATE_ITEM_OPTION_STRING_BEFORE_DELETED = 'TEMPLATE_ITEM_OPTION_STRING_BEFORE_DELETED';
    
    /* PAGE */
    const PAGE_CREATED = 'PAGE_CREATED';
    const PAGE_BEFORE_UPDATED = 'PAGE_BEFORE_UPDATED';
    const PAGE_UPDATED = 'PAGE_UPDATED';
    const PAGE_SAVED = 'PAGE_SAVED';
    const PAGE_BEFORE_DELETED = 'PAGE_BEFORE_DELETED';
    const PAGE_DELETED = 'PAGE_DELETED';

    /* PAGE ITEM */
    const PAGE_ITEM_CREATED = 'PAGE_ITEM_CREATED';
    const PAGE_ITEM_BEFORE_UPDATED = 'PAGE_ITEM_BEFORE_UPDATED';
    const PAGE_ITEM_UPDATED = 'PAGE_ITEM_UPDATED';
    const PAGE_ITEM_SAVED = 'PAGE_ITEM_SAVED';
    const PAGE_ITEM_BEFORE_DELETED = 'PAGE_ITEM_BEFORE_DELETED';
    
    /* PAGE ITEM OPTION */
    const PAGE_ITEM_OPTION_CREATED = 'PAGE_ITEM_OPTION_CREATED';
    const PAGE_ITEM_OPTION_BEFORE_UPDATED = 'PAGE_ITEM_OPTION_BEFORE_UPDATED';
    const PAGE_ITEM_OPTION_UPDATED = 'PAGE_ITEM_OPTION_UPDATED';
    const PAGE_ITEM_OPTION_SAVED = 'PAGE_ITEM_OPTION_SAVED';
    const PAGE_ITEM_OPTION_BEFORE_DELETED = 'PAGE_ITEM_OPTION_BEFORE_DELETED';

    /* PAGE_ITEM OPTION ITEM DATE */
    const PAGE_ITEM_OPTION_DATE_CREATED = 'PAGE_ITEM_OPTION_DATE_CREATED';
    const PAGE_ITEM_OPTION_DATE_BEFORE_UPDATED = 'PAGE_ITEM_OPTION_DATE_BEFORE_UPDATED';
    const PAGE_ITEM_OPTION_DATE_UPDATED = 'PAGE_ITEM_OPTION_DATE_UPDATED';
    const PAGE_ITEM_OPTION_DATE_SAVED = 'PAGE_ITEM_OPTION_DATE_SAVED';
    const PAGE_ITEM_OPTION_DATE_BEFORE_DELETED = 'PAGE_ITEM_OPTION_DATE_BEFORE_DELETED';

    /* PAGE_ITEM OPTION ITEM DECIMAL */
    const PAGE_ITEM_OPTION_DECIMAL_CREATED = 'PAGE_ITEM_OPTION_DECIMAL_CREATED';
    const PAGE_ITEM_OPTION_DECIMAL_BEFORE_UPDATED = 'PAGE_ITEM_OPTION_DECIMAL_BEFORE_UPDATED';
    const PAGE_ITEM_OPTION_DECIMAL_UPDATED = 'PAGE_ITEM_OPTION_DECIMAL_UPDATED';
    const PAGE_ITEM_OPTION_DECIMAL_SAVED = 'PAGE_ITEM_OPTION_DECIMAL_SAVED';
    const PAGE_ITEM_OPTION_DECIMAL_BEFORE_DELETED = 'PAGE_ITEM_OPTION_DECIMAL_BEFORE_DELETED';

    /* PAGE_ITEM OPTION ITEM INTEGER */
    const PAGE_ITEM_OPTION_INTEGER_CREATED = 'PAGE_ITEM_OPTION_INTEGER_CREATED';
    const PAGE_ITEM_OPTION_INTEGER_BEFORE_UPDATED = 'PAGE_ITEM_OPTION_INTEGER_BEFORE_UPDATED';
    const PAGE_ITEM_OPTION_INTEGER_UPDATED = 'PAGE_ITEM_OPTION_INTEGER_UPDATED';
    const PAGE_ITEM_OPTION_INTEGER_SAVED = 'PAGE_ITEM_OPTION_INTEGER_SAVED';
    const PAGE_ITEM_OPTION_INTEGER_BEFORE_DELETED = 'PAGE_ITEM_OPTION_INTEGER_BEFORE_DELETED';

    /* PAGE_ITEM OPTION ITEM STRING */
    const PAGE_ITEM_OPTION_STRING_CREATED = 'PAGE_ITEM_OPTION_STRING_CREATED';
    const PAGE_ITEM_OPTION_STRING_BEFORE_UPDATED = 'PAGE_ITEM_OPTION_STRING_BEFORE_UPDATED';
    const PAGE_ITEM_OPTION_STRING_UPDATED = 'PAGE_ITEM_OPTION_STRING_UPDATED';
    const PAGE_ITEM_OPTION_STRING_SAVED = 'PAGE_ITEM_OPTION_STRING_SAVED';
    const PAGE_ITEM_OPTION_STRING_BEFORE_DELETED = 'PAGE_ITEM_OPTION_STRING_BEFORE_DELETED';
    
    /* LANGUAGE */
    const LANGUAGE_CREATED = 'LANGUAGE_CREATED';
    const LANGUAGE_BEFORE_UPDATED = 'LANGUAGE_BEFORE_UPDATED';
    const LANGUAGE_UPDATED = 'LANGUAGE_UPDATED';
    const LANGUAGE_SAVED = 'LANGUAGE_SAVED';
    const LANGUAGE_BEFORE_DELETED = 'LANGUAGE_BEFORE_DELETED';
    
    /* SITE TRANSLATION */
    const SITE_TRANSLATION_CREATED = 'SITE_TRANSLATION_CREATED';
    const SITE_TRANSLATION_BEFORE_UPDATED = 'SITE_TRANSLATION_BEFORE_UPDATED';
    const SITE_TRANSLATION_UPDATED = 'SITE_TRANSLATION_UPDATED';
    const SITE_TRANSLATION_SAVED = 'SITE_TRANSLATION_SAVED';
    const SITE_TRANSLATION_BEFORE_DELETED = 'SITE_TRANSLATION_BEFORE_DELETED';
    
    /* SITE LANGUAGES */
    const SITE_LANGUAGE_CREATED = 'SITE_LANGUAGE_CREATED';
    const SITE_LANGUAGE_BEFORE_UPDATED = 'SITE_LANGUAGE_BEFORE_UPDATED';
    const SITE_LANGUAGE_UPDATED = 'SITE_LANGUAGE_UPDATED';
    const SITE_LANGUAGE_SAVED = 'SITE_LANGUAGE_SAVED';
    const SITE_LANGUAGE_BEFORE_DELETED = 'SITE_LANGUAGE_BEFORE_DELETED';

    /* UPLOADS */
    const FILE = 'file';
    const DIRECTORY = 'directory';

    /* SUBMISSIONS EXCEL */
    const EXCEL_SUBMISSION_DATE = '_submission_date_';

    public function getFields()
    {
        return [
            'SYSTEM',
            'RECOVERABLE_ID',
            'FORM_SUBMIT',
            'DATABASE_BACKUP_DATE_FORMAT',
            
            'USER_CREATED',
            'USER_BEFORE_UPDATED',
            'USER_UPDATED',
            'USER_SAVED',
            'USER_BEFORE_DELETED',
            
            'CMS_ROLE_CREATED',
            'CMS_ROLE_BEFORE_UPDATED',
            'CMS_ROLE_UPDATED',
            'CMS_ROLE_SAVED',
            'CMS_ROLE_BEFORE_DELETED',

            'SITE_CREATED',
            'SITE_BEFORE_UPDATED',
            'SITE_UPDATED',
            'SITE_SAVED',
            'SITE_BEFORE_DELETED',

            'COMPONENT_CREATED',
            'COMPONENT_BEFORE_UPDATED',
            'COMPONENT_UPDATED',
            'COMPONENT_SAVED',
            'COMPONENT_BEFORE_DELETED',
            'COMPONENT_CASCADE_UPDATE_INHERITANCES',

            'COMPONENT_OPTION_CREATED',
            'COMPONENT_OPTION_BEFORE_UPDATED',
            'COMPONENT_OPTION_UPDATED',
            'COMPONENT_OPTION_SAVED',
            'COMPONENT_OPTION_BEFORE_DELETED',
            
            'COMPONENT_OPTION_DATE_CREATED',
            'COMPONENT_OPTION_DATE_BEFORE_UPDATED',
            'COMPONENT_OPTION_DATE_UPDATED',
            'COMPONENT_OPTION_DATE_SAVED',
            'COMPONENT_OPTION_DATE_BEFORE_DELETED',
            
            'COMPONENT_OPTION_DECIMAL_CREATED',
            'COMPONENT_OPTION_DECIMAL_BEFORE_UPDATED',
            'COMPONENT_OPTION_DECIMAL_UPDATED',
            'COMPONENT_OPTION_DECIMAL_SAVED',
            'COMPONENT_OPTION_DECIMAL_BEFORE_DELETED',
            
            'COMPONENT_OPTION_INTEGER_CREATED',
            'COMPONENT_OPTION_INTEGER_BEFORE_UPDATED',
            'COMPONENT_OPTION_INTEGER_UPDATED',
            'COMPONENT_OPTION_INTEGER_SAVED',
            'COMPONENT_OPTION_INTEGER_BEFORE_DELETED',
            
            'COMPONENT_OPTION_STRING_CREATED',
            'COMPONENT_OPTION_STRING_BEFORE_UPDATED',
            'COMPONENT_OPTION_STRING_UPDATED',
            'COMPONENT_OPTION_STRING_SAVED',
            'COMPONENT_OPTION_STRING_BEFORE_DELETED',

            'REDIRECT_URL_CREATED',
            'REDIRECT_URL_BEFORE_UPDATED',
            'REDIRECT_URL_UPDATED',
            'REDIRECT_URL_SAVED',
            'REDIRECT_URL_BEFORE_DELETED',

            'GLOBAL_ITEM_CREATED',
            'GLOBAL_ITEM_BEFORE_UPDATED',
            'GLOBAL_ITEM_UPDATED',
            'GLOBAL_ITEM_SAVED',
            'GLOBAL_ITEM_BEFORE_DELETED',
            'GLOBAL_ITEM_DELETED',

            'GLOBAL_ITEM_OPTION_CREATED',
            'GLOBAL_ITEM_OPTION_BEFORE_UPDATED',
            'GLOBAL_ITEM_OPTION_UPDATED',
            'GLOBAL_ITEM_OPTION_SAVED',
            'GLOBAL_ITEM_OPTION_BEFORE_DELETED',

            'GLOBAL_ITEM_OPTION_DATE_CREATED',
            'GLOBAL_ITEM_OPTION_DATE_BEFORE_UPDATED',
            'GLOBAL_ITEM_OPTION_DATE_UPDATED',
            'GLOBAL_ITEM_OPTION_DATE_SAVED',
            'GLOBAL_ITEM_OPTION_DATE_BEFORE_DELETED',

            'GLOBAL_ITEM_OPTION_DECIMAL_CREATED',
            'GLOBAL_ITEM_OPTION_DECIMAL_BEFORE_UPDATED',
            'GLOBAL_ITEM_OPTION_DECIMAL_UPDATED',
            'GLOBAL_ITEM_OPTION_DECIMAL_SAVED',
            'GLOBAL_ITEM_OPTION_DECIMAL_BEFORE_DELETED',

            'GLOBAL_ITEM_OPTION_INTEGER_CREATED',
            'GLOBAL_ITEM_OPTION_INTEGER_BEFORE_UPDATED',
            'GLOBAL_ITEM_OPTION_INTEGER_UPDATED',
            'GLOBAL_ITEM_OPTION_INTEGER_SAVED',
            'GLOBAL_ITEM_OPTION_INTEGER_BEFORE_DELETED',

            'GLOBAL_ITEM_OPTION_STRING_CREATED',
            'GLOBAL_ITEM_OPTION_STRING_BEFORE_UPDATED',
            'GLOBAL_ITEM_OPTION_STRING_UPDATED',
            'GLOBAL_ITEM_OPTION_STRING_SAVED',
            'GLOBAL_ITEM_OPTION_STRING_BEFORE_DELETED',

            'TEMPLATE_CREATED',
            'TEMPLATE_BEFORE_UPDATED',
            'TEMPLATE_UPDATED',
            'TEMPLATE_SAVED',
            'TEMPLATE_BEFORE_DELETED',

            'TEMPLATE_ITEM_CREATED',
            'TEMPLATE_ITEM_BEFORE_UPDATED',
            'TEMPLATE_ITEM_UPDATED',
            'TEMPLATE_ITEM_SAVED',
            'TEMPLATE_ITEM_BEFORE_DELETED',

            'TEMPLATE_ITEM_OPTION_CREATED',
            'TEMPLATE_ITEM_OPTION_BEFORE_UPDATED',
            'TEMPLATE_ITEM_OPTION_UPDATED',
            'TEMPLATE_ITEM_OPTION_SAVED',
            'TEMPLATE_ITEM_OPTION_BEFORE_DELETED',

            'TEMPLATE_ITEM_OPTION_DATE_CREATED',
            'TEMPLATE_ITEM_OPTION_DATE_BEFORE_UPDATED',
            'TEMPLATE_ITEM_OPTION_DATE_UPDATED',
            'TEMPLATE_ITEM_OPTION_DATE_SAVED',
            'TEMPLATE_ITEM_OPTION_DATE_BEFORE_DELETED',

            'TEMPLATE_ITEM_OPTION_DECIMAL_CREATED',
            'TEMPLATE_ITEM_OPTION_DECIMAL_BEFORE_UPDATED',
            'TEMPLATE_ITEM_OPTION_DECIMAL_UPDATED',
            'TEMPLATE_ITEM_OPTION_DECIMAL_SAVED',
            'TEMPLATE_ITEM_OPTION_DECIMAL_BEFORE_DELETED',

            'TEMPLATE_ITEM_OPTION_INTEGER_CREATED',
            'TEMPLATE_ITEM_OPTION_INTEGER_BEFORE_UPDATED',
            'TEMPLATE_ITEM_OPTION_INTEGER_UPDATED',
            'TEMPLATE_ITEM_OPTION_INTEGER_SAVED',
            'TEMPLATE_ITEM_OPTION_INTEGER_BEFORE_DELETED',

            'TEMPLATE_ITEM_OPTION_STRING_CREATED',
            'TEMPLATE_ITEM_OPTION_STRING_BEFORE_UPDATED',
            'TEMPLATE_ITEM_OPTION_STRING_UPDATED',
            'TEMPLATE_ITEM_OPTION_STRING_SAVED',
            'TEMPLATE_ITEM_OPTION_STRING_BEFORE_DELETED',

            'PAGE_CREATED',
            'PAGE_BEFORE_UPDATED',
            'PAGE_UPDATED',
            'PAGE_SAVED',
            'PAGE_BEFORE_DELETED',
            'PAGE_DELETED',

            'PAGE_ITEM_CREATED',
            'PAGE_ITEM_BEFORE_UPDATED',
            'PAGE_ITEM_UPDATED',
            'PAGE_ITEM_SAVED',
            'PAGE_ITEM_BEFORE_DELETED',

            'PAGE_ITEM_OPTION_CREATED',
            'PAGE_ITEM_OPTION_BEFORE_UPDATED',
            'PAGE_ITEM_OPTION_UPDATED',
            'PAGE_ITEM_OPTION_SAVED',
            'PAGE_ITEM_OPTION_BEFORE_DELETED',

            'PAGE_ITEM_OPTION_DATE_CREATED',
            'PAGE_ITEM_OPTION_DATE_BEFORE_UPDATED',
            'PAGE_ITEM_OPTION_DATE_UPDATED',
            'PAGE_ITEM_OPTION_DATE_SAVED',
            'PAGE_ITEM_OPTION_DATE_BEFORE_DELETED',

            'PAGE_ITEM_OPTION_DECIMAL_CREATED',
            'PAGE_ITEM_OPTION_DECIMAL_BEFORE_UPDATED',
            'PAGE_ITEM_OPTION_DECIMAL_UPDATED',
            'PAGE_ITEM_OPTION_DECIMAL_SAVED',
            'PAGE_ITEM_OPTION_DECIMAL_BEFORE_DELETED',

            'PAGE_ITEM_OPTION_INTEGER_CREATED',
            'PAGE_ITEM_OPTION_INTEGER_BEFORE_UPDATED',
            'PAGE_ITEM_OPTION_INTEGER_UPDATED',
            'PAGE_ITEM_OPTION_INTEGER_SAVED',
            'PAGE_ITEM_OPTION_INTEGER_BEFORE_DELETED',

            'PAGE_ITEM_OPTION_STRING_CREATED',
            'PAGE_ITEM_OPTION_STRING_BEFORE_UPDATED',
            'PAGE_ITEM_OPTION_STRING_UPDATED',
            'PAGE_ITEM_OPTION_STRING_SAVED',
            'PAGE_ITEM_OPTION_STRING_BEFORE_DELETED',
            
            'LANGUAGE_CREATED',
            'LANGUAGE_BEFORE_UPDATED',
            'LANGUAGE_UPDATED',
            'LANGUAGE_SAVED',
            'LANGUAGE_BEFORE_DELETED',
            
            'SITE_TRANSLATION_CREATED',
            'SITE_TRANSLATION_BEFORE_UPDATED',
            'SITE_TRANSLATION_UPDATED',
            'SITE_TRANSLATION_SAVED',
            'SITE_TRANSLATION_BEFORE_DELETED',
            
            'SITE_LANGUAGE_CREATED',
            'SITE_LANGUAGE_BEFORE_UPDATED',
            'SITE_LANGUAGE_UPDATED',
            'SITE_LANGUAGE_SAVED',
            'SITE_LANGUAGE_BEFORE_DELETED',
        ];
    }
}