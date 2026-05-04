<?php

declare(strict_types=1);

namespace Sunnysideup\MailCapture\Controller;

use SilverStripe\Control\Controller;
use SilverStripe\Security\PermissionProvider;
use Sunnysideup\MailCapture\Model\CapturedEmail;

/**
 * Controller for viewing a previously captured email as the client would see it
 *
 */
class CapturedEmailController extends Controller implements PermissionProvider
{
    private static $allowed_actions = ['view' => 'CMS_ACCESS_MailCaptureAdmin'];

    public function providePermissions()
    {
        return [
            'CMS_ACCESS_MailCaptureAdmin' => 'View MailCapture records'
        ];
    }

    public function view()
    {
        $id = (int) $this->getRequest()->param('ID');

        if ($id !== 0) {
            $email = CapturedEmail::get()->byID($id);
            if ($email) {
                return ['Email' => $email];
            }
        }

        return null;
    }
}
