<?php

namespace App\Api\Listeners;

use App\Api\Constants\ValidationRuleConstants;
use App\Api\Events\FormEmailSent;
use /** @noinspection PhpUnusedAliasInspection */
    Illuminate\Queue\InteractsWithQueue;
use /** @noinspection PhpUnusedAliasInspection */
    Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use \Swift_Mailer;
use \Swift_SmtpTransport;

class SendFormEmailNotification
{
    /**
     * @var $log
     */
    private $log;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  FormEmailSent $event
     * @return void
     */
    public function handle(FormEmailSent $event)
    {
        /** @var \App\Api\Models\Site $site */
        $site = $event->site;
        /** @var \App\Api\Models\CmsLog $log */
        $this->log = $event->log;
        $properties = $event->properties;
        $formTypeValue = $event->formTypeValue ?: '';
        $submissionData = $event->submissionData;
        $overrides = $event->overrides ?: [];

        // SMTP
        $smtpHost = config('cms.' . get_cms_application() . '.smtp_host');
        $smtpPort = intval(config('cms.' . get_cms_application() . '.smtp_port', 587));
        $smtpUsername = config('cms.' . get_cms_application() . '.smtp_username');
        $smtpPassword = config('cms.' . get_cms_application() . '.smtp_password');

        // Postmark
        $postmarkEmail = config('cms.' . get_cms_application() . '.postmark_app_email');
        $postmarkToken = config('cms.' . get_cms_application() . '.postmark_app_token');

        if (array_key_exists(ValidationRuleConstants::FORM_OVERRIDE_SENDER_EMAIL_KEY, $overrides)
            && ! empty($overrides[ValidationRuleConstants::FORM_OVERRIDE_SENDER_EMAIL_KEY])
        ) {
            $senderEmail = $overrides[ValidationRuleConstants::FORM_OVERRIDE_SENDER_EMAIL_KEY];
        } else {
            $senderEmail = $postmarkEmail;
        }

        if (empty($senderEmail)) {
            return;
        }

        $mailerBackup = null;

        if ( ! empty($smtpHost) && ! empty($smtpPort) && ! empty($smtpUsername) && ! empty($smtpPassword)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $mailerBackup = Mail::getSwiftMailer();
            $transport = Swift_SmtpTransport::newInstance($smtpHost, $smtpPort, 'tls');
            $transport->setUsername($smtpUsername);
            $transport->setPassword($smtpPassword);
            $transport->setAuthMode('login');
            $mailer = new Swift_Mailer($transport);
            /** @noinspection PhpUndefinedMethodInspection */
            Mail::setSwiftMailer($mailer);
        } else if ( ! empty($postmarkToken)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $mailerBackup = Mail::getSwiftMailer();
            $transport = Swift_SmtpTransport::newInstance('smtp.postmarkapp.com', 587, 'tls');
            $transport->setUsername($postmarkToken);
            $transport->setPassword($postmarkToken);
            $transport->setAuthMode('login');
            $mailer = new Swift_Mailer($transport);
            /** @noinspection PhpUndefinedMethodInspection */
            Mail::setSwiftMailer($mailer);
        }

        $sendToTargetEmails = collect($properties)
            ->where('variable_name', 'send_to_target_emails')
            ->first();

        if ( ! empty($sendToTargetEmails)) {
            $sendToTargetEmailsValue = array_key_exists('translated_text', $sendToTargetEmails)
                ? $sendToTargetEmails['translated_text']
                : $sendToTargetEmails['option_value'];

            if (to_boolean($sendToTargetEmailsValue)) {
                $sendData = [];
                $sendData['siteDomainName'] = ucfirst(preg_replace('/-|_/', ' ', $site->domain_name));
                $sendData['submissionName'] = $formTypeValue;
                $sendData['data'] = $submissionData;

                $siteDomainName = $sendData['siteDomainName'];
                $submissionName = $sendData['submissionName'];

                $targetNotifyTemplate = collect($properties)
                    ->where('variable_name', 'target_notify_template')
                    ->first();

                $targetNotifyTemplateValue = ( ! empty($targetNotifyTemplate))
                    ? array_key_exists('translated_text', $targetNotifyTemplate)
                        ? $targetNotifyTemplate['translated_text']
                        : $targetNotifyTemplate['option_value']
                    : '';

                if ( ! empty($targetNotifyTemplateValue)) {
                    $resolved = $this->bindEmailTemplate($targetNotifyTemplateValue, $submissionData);
                    $body = view('preview', ['preview' => html_entity_decode(htmlspecialchars($resolved))])->render();
                } else {
                    $templatePath = 'emails.' . get_cms_application() . '.' . $site->domain_name . '.form_target_template';

                    if ( ! view()->exists($templatePath)) {
                        $templatePath = 'emails.form_target_template';
                    }

                    $body = view($templatePath, $sendData)->render();
                }

                $subjectValue = "New Submission to $siteDomainName - $submissionName";

                if ($subject = collect($properties)->where('variable_name', 'target_notify_subject')->first()) {
                    if (array_key_exists('translated_text', $subject) && !empty($subject['translated_text'])) {
                        $subjectValue = trim($subject['translated_text']);
                    } else if (array_key_exists('option_value', $subject) && !empty($subject['option_value'])) {
                        $subjectValue = trim($subject['option_value']);
                    }
                }

                $subjectValue = $this->bindEmailTemplate($subjectValue, $submissionData);

                $body = "<html><body>$body</body></html>";

                $to = collect($properties)
                    ->where('variable_name', 'target_emails')
                    ->first();
                $toValue = ( ! empty($to))
                    ? array_key_exists('translated_text', $to)
                        ? trim($to['translated_text'])
                        : trim($to['option_value'])
                    : '';

                if (array_key_exists(ValidationRuleConstants::FORM_OVERRIDE_RECIPIENT_EMAILS_KEY, $overrides)
                    && ! empty($overrides[ValidationRuleConstants::FORM_OVERRIDE_RECIPIENT_EMAILS_KEY])
                ) {

                    $toValue = $overrides[ValidationRuleConstants::FORM_OVERRIDE_RECIPIENT_EMAILS_KEY];
                }

                $cc = collect($properties)
                    ->where('variable_name', 'target_email_cc')
                    ->first();
                $ccValue = ( ! empty($cc))
                    ? array_key_exists('translated_text', $cc)
                        ? trim($cc['translated_text'])
                        : trim($cc['option_value'])
                    : '';

                if (array_key_exists(ValidationRuleConstants::FORM_OVERRIDE_CC_EMAILS_KEY, $overrides)
                    && ! empty($overrides[ValidationRuleConstants::FORM_OVERRIDE_CC_EMAILS_KEY])
                ) {

                    $ccValue = $overrides[ValidationRuleConstants::FORM_OVERRIDE_CC_EMAILS_KEY];
                }

                $bcc = collect($properties)
                    ->where('variable_name', 'target_email_bcc')
                    ->first();
                $bccValue = ( ! empty($bcc))
                    ? array_key_exists('translated_text', $bcc)
                        ? trim($bcc['translated_text'])
                        : trim($bcc['option_value'])
                    : '';

                if (array_key_exists(ValidationRuleConstants::FORM_OVERRIDE_BCC_EMAILS_KEY, $overrides)
                    && ! empty($overrides[ValidationRuleConstants::FORM_OVERRIDE_BCC_EMAILS_KEY])
                ) {

                    $bccValue = $overrides[ValidationRuleConstants::FORM_OVERRIDE_BCC_EMAILS_KEY];
                }

                $toEmails = empty($toValue) ? null : explode(',', $toValue);
                $ccEmails = empty($ccValue) ? [] : explode(',', $ccValue);
                $bccEmails = empty($bccValue) ? [] : explode(',', $bccValue);

                if ( ! empty($mailer) && ! empty($toValue)) {
                            /** @noinspection PhpUndefinedMethodInspection */
                    Mail::send('preview', ['preview' => html_entity_decode(htmlspecialchars($body))],
                        function ($message) use ($senderEmail, $toEmails, $ccEmails, $bccEmails, $subjectValue) {
                            /** @noinspection PhpUndefinedMethodInspection */
                        $message->from($senderEmail)
                            ->to($toEmails)
                            ->cc($ccEmails)
                            ->bcc($bccEmails)
                            ->subject($subjectValue);
                        }
                    );
                }
            }
        }

        $userNotification = collect($properties)
            ->where('variable_name', 'user_notification')
            ->first();

        if ( ! empty($userNotification)) {
            $userNotificationValue = array_key_exists('translated_text', $userNotification)
                ? $userNotification['translated_text']
                : $userNotification['option_value'];

            if (to_boolean($userNotificationValue)) {
                if ($to = collect($submissionData)
                    ->whereIn('name', [
                        ValidationRuleConstants::FORM_USER_NOTIFICATION_EMAIL_VARIABLE_NAME,
                        ValidationRuleConstants::FORM_USER_NOTIFICATION_EMAILS_VARIABLE_NAME
                    ])->first()
                ) {

                    $toValue = ( !empty($to)) ? trim($to['value']) : '';

                    $sendData = [];
                    $sendData['siteDomainName'] = ucfirst(preg_replace('/-|_/', ' ', $site->domain_name));
                    $sendData['submissionName'] = $formTypeValue;
                    $sendData['data'] = $submissionData;

                    $siteDomainName = $sendData['siteDomainName'];
                    $submissionName = $sendData['submissionName'];

                    $userNotifyTemplate = collect($properties)
                        ->where('variable_name', 'user_notify_template')
                        ->first();

                    $userNotifyTemplateValue = ( ! empty($userNotifyTemplate))
                        ? array_key_exists('translated_text', $userNotifyTemplate)
                            ? $userNotifyTemplate['translated_text']
                            : $userNotifyTemplate['option_value']
                        : '';

                    if ( ! empty($userNotifyTemplateValue)) {
                        $resolved = $this->bindEmailTemplate($userNotifyTemplateValue, $submissionData);
                        $body = view('preview', ['preview' => html_entity_decode(htmlspecialchars($resolved))])->render();
                    } else {
                        $templatePath = 'emails.' . get_cms_application() . '.' . $site->domain_name . '.user_notification_template';

                        if ( ! view()->exists($templatePath)) {
                            $templatePath = 'emails.user_notification_template';
                        }

                        $body = view($templatePath, $sendData)->render();
                    }

                    $subjectValue = "New Submission to $siteDomainName - $submissionName";

                    if ($subject = collect($properties)->where('variable_name', 'user_notify_subject')->first()) {
                        if (array_key_exists('translated_text', $subject) && !empty($subject['translated_text'])) {
                            $subjectValue = trim($subject['translated_text']);
                        } else if (array_key_exists('option_value', $subject) && !empty($subject['option_value'])) {
                            $subjectValue = trim($subject['option_value']);
                        }
                    }

                    $subjectValue = $this->bindEmailTemplate($subjectValue, $submissionData);

                    $body = "<html><body>$body</body></html>";

                    $toEmails = empty($toValue) ? null : explode(',', $toValue);

                    if ( ! empty($mailer) && ! empty($toValue)) {
                            /** @noinspection PhpUndefinedMethodInspection */
                        Mail::send('preview', ['preview' => html_entity_decode(htmlspecialchars($body))],
                            function ($message) use ($senderEmail, $toEmails, $subjectValue) {
                                /** @noinspection PhpUndefinedMethodInspection */
                                $message->from($senderEmail)->to($toEmails)->subject($subjectValue);
                        }
                        );
                    }
                }
            }
        }

        if ( ! empty($mailerBackup)) {
            /** @noinspection PhpUndefinedMethodInspection */
            Mail::setSwiftMailer($mailerBackup);
        }
    }

    /**
     * @param $htmlString
     * @param $data
     * @return mixed|string
     */
    private function bindEmailTemplate($htmlString, $data)
    {
        if (empty($htmlString) || empty($data)) return $htmlString;

        foreach ($data as $key => $obj) {
            if (array_key_exists('value', $obj)) {
                $value = is_array($obj['value']) ? join(', ', $obj['value']) : $obj['value'];
                if (is_null($obj['value']) || $obj['value'] === '') {
                    $htmlString = preg_replace('/\${\s*' . $key . '\s*(?:\|\s*([\w\s]*)\s*)}/', '$1', $htmlString);
                    $htmlString = preg_replace('/\${\s*' . $key . '\s*}/', $value, $htmlString);
                    // Backward compatibility
                    $htmlString = preg_replace('/{{\s*' . $key . '\s*(?:\|\s*([\w\s]*)\s*)}}/', '$1', $htmlString);
                    $htmlString = preg_replace('/{{\s*' . $key . '\s*}}/', $value, $htmlString);
                } else {
                    $htmlString = preg_replace('/\${\s*' . $key . '\s*(?:\|\s*([\w\s]*)\s*)?}/', $value, $htmlString);
                    // Backward compatibility
                    $htmlString = preg_replace('/{{\s*' . $key . '\s*(?:\|\s*([\w\s]*)\s*)?}}/', $value, $htmlString);
                }
            }
        }

        $htmlString = preg_replace('/\${\s*\w+\s*(?:\|\s*([\w\s]*)\s*)}/', '$1', $htmlString);
        $htmlString = preg_replace('/\${\s*\w+\s*}/', '', $htmlString);
        // Backward compatibility
        $htmlString = preg_replace('/{{\s*\w+\s*(?:\|\s*([\w\s]*)\s*)}}/', '$1', $htmlString);
        $htmlString = preg_replace('/{{\s*\w+\s*}}/', '', $htmlString);

        // Submit date $submissionDate
        $submitDate = $this->log->created_at->format('Y-m-d H:i:s T');
        $htmlString = preg_replace('/\$submissionDate/', $submitDate, $htmlString);

        return $htmlString;
    }
}
