<?php

namespace Sunnysideup\MailCapture\BuildTask;

use Symfony\Component\Console\Input\InputInterface;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use SilverStripe\Dev\BuildTask;
use SilverStripe\Security\Permission;
use Sunnysideup\MailCapture\Model\CapturedEmail;

/**
 * @author <marcus@silverstripe.com.au>
 * @license BSD License http://www.silverstripe.org/bsd-license
 */
class PruneEmailsTask extends BuildTask
{
    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        if (Permission::check('ADMIN')) {
            $since = date('Y-m-d H:i:s', strtotime('-1 month'));
            $list = CapturedEmail::get()->filter(['Created:LessThan' => $since]);
            echo "Deleting " . $list->count() . " captured emails (if ?confirm get var is set)<br/>\n";
            $list->removeAll();
        }
        return Command::SUCCESS;
    }
}
