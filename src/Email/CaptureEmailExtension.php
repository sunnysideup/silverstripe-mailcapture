<?php

namespace Sunnysideup\MailCapture\Email;

use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Extension;

use Sunnysideup\MailCapture\Model\CapturedEmail;

/**
 * A mailer that can be used to capture emails instead of sending them out
 *
 * @property Email|CaptureEmailExtension $owner
 */
class CaptureEmailExtension extends Extension
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
