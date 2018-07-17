<?php
namespace App\Api\Constants;

class ValidationRuleConstants extends EnumType
{
    const VARIABLE_NAME_REGEX = '/^[a-zA-Z]+[a-zA-Z_0-9]+[a-zA-Z0-9]+$/';
    const FORM_TOKEN_HEADER = 'X-CMS-Form-Token';
    const FORM_TOKEN_BODY = '_cms_form_token';
    const HONEY_POT_FIELD = '_honey_pot';
    const GOOGLE_RECAPTCHA_RESPONSE = 'g-recaptcha-response';
    const CMS_APPLICATION_NAME_HEADER = 'X-CMS-Application-Name';
    const CMS_APPLICATION_NAME_FIELD = '_cms_application_name';
    const FORM_USER_NOTIFICATION_EMAIL_VARIABLE_NAME = 'email';
    const FORM_USER_NOTIFICATION_EMAILS_VARIABLE_NAME = 'emails';
    const FORM_PROPERTIES = 'form_properties';
    const FORM_PRIVATE_PROPERTIES = [
            'form_type',
            'target_emails',
            'target_notify_subject',
            'target_notify_template',
            'target_email_cc',
            'target_email_bcc',
            'send_to_target_emails',
            'notify_template',
            'user_notify_subject',
            'user_notify_template',
            'user_notification',
            'form_properties',
            'submit_button_label'
        ];

    const FORM_OVERRIDE_SENDER_EMAIL = '_form_override_sender_email';
    const FORM_OVERRIDE_SENDER_EMAIL_KEY = 'sender';
    const FORM_OVERRIDE_RECIPIENT_EMAILS = '_form_override_recipient_emails';
    const FORM_OVERRIDE_RECIPIENT_EMAILS_KEY = 'recipients';
    const FORM_OVERRIDE_CC_EMAILS = '_form_override_cc_emails';
    const FORM_OVERRIDE_CC_EMAILS_KEY = 'cc';
    const FORM_OVERRIDE_BCC_EMAILS = '_form_override_bcc_emails';
    const FORM_OVERRIDE_BCC_EMAILS_KEY = 'bcc';

    const RESERVED_FRIENDLY_URL = [
        'api',
        'client-api',
        'system'
    ];

    public function getFields()
    {
        return [
            'VARIABLE_NAME_REGEX',
            'FORM_TOKEN_HEADER',
            'FORM_TOKEN_BODY',
            'HONEY_POT_FIELD',
            'GOOGLE_RECAPTCHA_RESPONSE',
            'CMS_APPLICATION_NAME_HEADER',
            'CMS_APPLICATION_NAME_FIELD',
            'FORM_USER_NOTIFICATION_EMAIL_VARIABLE_NAME',
            'FORM_USER_NOTIFICATION_EMAILS_VARIABLE_NAME',
            'FORM_PROPERTIES',
            'FORM_PRIVATE_PROPERTIES',

            'FORM_OVERRIDE_SENDER_EMAIL',
            'FORM_OVERRIDE_SENDER_EMAIL_KEY',
            'FORM_OVERRIDE_RECIPIENT_EMAILS',
            'FORM_OVERRIDE_RECIPIENT_EMAILS_KEY',
            'FORM_OVERRIDE_CC_EMAILS',
            'FORM_OVERRIDE_CC_EMAILS_KEY',
            'FORM_OVERRIDE_BCC_EMAILS',
            'FORM_OVERRIDE_BCC_EMAILS_KEY',

            'RESERVED_FRIENDLY_URL',
        ];
    }
}