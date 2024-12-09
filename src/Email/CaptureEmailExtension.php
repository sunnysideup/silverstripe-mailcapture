<?php

namespace Sunnysideup\MailCapture\Email;

use SilverStripe\Control\Email\Email;
use SilverStripe\ORM\DataExtension;
use Symfony\Component\Mailer\Mailer;

use SilverStripe\Control\Email\SwiftMailer;
use SilverStripe\Core\Extension;
use Sunnysideup\MailCapture\Model\CapturedEmail;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Rawowner;

/**
 * A mailer that can be used to capture emails instead of sending them out
 *
 * @property Email|CaptureEmailExtension $owner
 */
class CaptureEmailExtension extends DataExtension
{
    /**
     * Undocumented function
     *
     * @param ViewableData $data
     * @return void
     */
    public function updateGetData($data): void
    {
        $owner = $this->getOwner();
        CapturedEmail::record_email($owner, $data);
    }
}
