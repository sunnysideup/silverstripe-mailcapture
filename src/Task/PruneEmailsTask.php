<?php

declare(strict_types=1);

namespace Sunnysideup\MailCapture\BuildTask;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\Security\Permission;
use Sunnysideup\MailCapture\Model\CapturedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * @author <marcus@silverstripe.com.au>
 * @license BSD License http://www.silverstripe.org/bsd-license
 */
class PruneEmailsTask extends BuildTask
{
    protected static string $commandName = 'prune-emails';

    protected string $title = 'Prune old captured emails';

    protected static string $description = 'Deletes captured emails older than 1 month';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        if (!Permission::check('ADMIN')) {
            $output->writeln('<error>You must be an admin to run this task</error>');
            return Command::FAILURE;
        }

        $since = date('Y-m-d H:i:s', strtotime('-1 month'));
        $list = CapturedEmail::get()->filter(['Created:LessThan' => $since]);
        $count = $list->count();

        if ($input->getOption('confirm')) {
            $output->writeln("Deleting {$count} captured emails...");
            $list->removeAll();
            $output->writeln('<info>Emails deleted successfully</info>');
        } else {
            $output->writeln("Would delete {$count} captured emails. Use --confirm to actually delete them.");
        }

        return Command::SUCCESS;
    }

    public function getOptions(): array
    {
        return [
            new InputOption('confirm', 'c', InputOption::VALUE_NONE, 'Confirm deletion of emails'),
        ];
    }
}
