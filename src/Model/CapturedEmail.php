<?php

namespace Sunnysideup\MailCapture\Model;

use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;

use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Security\Security;

/**
 * Class \Sunnysideup\MailCapture\Model\CapturedEmail
 *
 * @property string $From
 * @property string $To
 * @property string $CC
 * @property string $BCC
 * @property string $ReplyTo
 * @property string $Subject
 * @property string $Headers
 * @property string $Content
 * @property string $PlainText
 * @property bool $Success
 * @property string $Error
 */
class CapturedEmail extends DataObject
{
    protected static $emails_send = [];
    protected static $shut = [];

    public static function record_email(Email $email, $data)
    {
        // function for processing from,to, cc, bcc


        $mail = CapturedEmail::create();
        $mail->Subject = $email->getSubject();
        $mail->From = self::formatEmailAddress($email->getFrom());
        $mail->To = self::formatEmailAddress($email->getTo());
        $mail->CC = self::formatEmailAddress($email->getCc());
        $mail->BCC = self::formatEmailAddress($email->getBcc());
        $mail->ReplyTo = self::formatEmailAddress($email->getReplyTo());

        // Ensure we can at least render template if any
        $htmlTemplate = $email->getHTMLTemplate();
        $plainTemplate = $email->getPlainTemplate();

        $plainContent = $htmlContent = '';
        // use html content with html template
        if ($htmlTemplate) {
            $htmlContent = $data->renderWith($htmlTemplate);
            $mail->Content = html_entity_decode($htmlContent);
        }
        // use plain content with plain template
        elseif ($plainTemplate) {
            $plainContent = $data->renderWith($plainTemplate);
            $mail->PlainText = $plainContent;
        }
        // default to same behaviour a prior to above implementation for templates so
        // as to not be a breaking change. We should probably decode this in the future.
        else {
            $mail->Content = $email->getBody();
        }
        $mail->write();

    }

    protected static function formatEmailAddress(array $emails): string
    {
        $return = '';
        if(is_string($emails)) {
            return $emails;
        } elseif(is_array($emails)) {
            foreach ($emails as $address => $title) {
                $return .= $title->getName();
                if ($title) {
                    $return .= " <".$title->getAddress().">";
                }
                $return .= ", ";
            }
        }
        return trim(trim(trim($return), ','));
    }

    private static $table_name = 'CapturedEmail';

    private static $db = array(
        'From'            => 'Varchar(128)',
        'To'              => 'Varchar(128)',
        'CC'              => 'Varchar(128)',
        'BCC'             => 'Varchar(128)',
        'ReplyTo'         => 'Varchar(128)',
        'Subject'         => 'Varchar(128)',
        'Headers'         => 'Text',
        'Content'         => 'Text',
        'PlainText'       => 'Text',
        'Success'         => 'Boolean',
        'Error'           => 'Text',
    );

    private static $summary_fields = array(
        'Created',
        'Subject',
        'From',
        'To',
        'CC',
        'BCC',
    );
    private static $searchable_fields = array(
        'Subject',
        'From',
        'To',
        'CC',
        'BCC',
    );

    private static $default_sort = 'ID DESC';

    public function canView($member = null)
    {
        if (!$member || !($member instanceof Member) || is_numeric($member)) {
            $member = Security::getCurrentUser();
        }
        if ($member && Permission::checkMember($member, array("ADMIN", "CMS_ACCESS_MailCaptureAdmin"))) {
            return true;
        }

        return parent::canView($member);
    }

    public function canEdit($member = null)
    {
        return false;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->replaceField(
            'Content',
            LiteralField::create(
                'ContentNice',
                '
                <div style="width: 100%; padding-bottom: 2rem;">
                    <label class="form__field-label">Email Content</label>
                    <iframe
                        style="display: block; width: 500px; margin-left: auto!important; margin-right: auto!important; height: 300px; border: 1px solid #ccc;"
                        srcdoc="' . Convert::raw2att($this->makeLinksClickable($this->Content)) . '">
                    </iframe>
                </div>
                '
            )
        );
        $fields->addFieldsToTab(
            'Root.Main',
            [
                ReadonlyField::create(
                    'Created',
                    'Sent'
                )
            ]
        );
        return $fields;
    }


    private function makeLinksClickable(string $text): string
    {
        $text = preg_replace(
            '#(script|about|applet|activex|chrome):#is',
            "\\1:",
            $text
        );

        $ret = ' ' . $text;

        // Replace Links with http://
        $ret = preg_replace(
            "#(^|[\n ])([\w]+?://[\w\#$%&~/.\-;:=,?@\[\]+]*)#is",
            "\\1<a href=\"\\2\" target=\"_blank\" rel=\"nofollow\">\\2</a>",
            $ret
        );

        // Replace Links without http://
        $ret = preg_replace(
            "#(^|[\n ])((www|ftp)\.[\w\#$%&~/.\-;:=,?@\[\]+]*)#is",
            "\\1<a href=\"http://\\2\" target=\"_blank\" rel=\"nofollow\">\\2</a>",
            $ret
        );

        // Replace Email Addresses
        $ret = preg_replace(
            "#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i",
            "\\1<a href=\"mailto:\\2@\\3\">\\2@\\3</a>",
            $ret
        );

        return substr($ret, 1);

    }

    protected function registerShutdown()
    {
        register_shutdown_function([$this, 'onShutdown']);
    }

    public function onShutdown(): void
    {
        $error = error_get_last();
        if ($error) {
            $this->Errors = serialize($error);
        } else {
            $this->Success = true;
        }
        $this->write();
    }


}
