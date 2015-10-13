<?php

namespace Bolt\Extension\Bolt\ClientLogin\Nut;

use Bolt\Nut\BaseCommand;
use Hautelook\Phpass\PasswordHash;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Nut command to set/reset a ClientLogin account password.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PasswordReset extends BaseCommand
{
    /**
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('clientlogin:reset')
            ->setDescription('Set or reset a ClientLogin account password')
            ->addOption('login', null, InputOption::VALUE_REQUIRED, 'Login name for the account')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password for the account')
        ;
    }

    /**
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $resourceOwnerId = $input->getOption('login');
        $password = $input->getOption('password');

        $hasher = new PasswordHash(12, true);
        $passwordHash = $hasher->HashPassword($password);

        if ($this->app['clientlogin.records']->setAccountPassword($resourceOwnerId, $passwordHash)) {
            $this->auditLog(__CLASS__, 'ClientLogin admin command set password for account: ' . $resourceOwnerId);
            $output->writeln("\n<info>Set password for account: {$resourceOwnerId}</info>");
        } else {
            $output->writeln("\n<error>Unable to set password for account: {$resourceOwnerId}</error>");
        }
    }
}
