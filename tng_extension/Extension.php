<?php

namespace Bolt\Extension\Bolt\ClientLogin;

use Bolt\BaseExtension;
use Bolt\Events\CronEvent;
use Bolt\Events\CronEvents;
use Bolt\Extension\Bolt\ClientLogin\Database\Schema;

/**
 * Frontend client login with OAuth2 or passwords.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 *
 * Based on the Bolt 1.5 extension 'Authenticate' by:
 * @author Lodewijk Evers
 * @author Tobias Dammers
 * @author Bob den Otter
 */
class Extension extends BaseExtension
{
    /** @var string Extension name */
    const NAME = 'ClientLogin';
    /** @var string Extension's container */
    const CONTAINER = 'extensions.ClientLogin';

    /** @var ClientLogin\Controller */
    private $controller;

    public function getName()
    {
        return Extension::NAME;
    }

    public function initialize()
    {
        // Service providers
        $this->app->register(new Provider\ServiceProvider($this->config));
        $this->app['twig']->addExtension(new Twig\ClientLoginExtension($this->app));
        $this->addNutCommands();

        // Check & create database tables if required
        if ($this->app['config']->getWhichEnd() === 'backend' || $this->app['config']->getWhichEnd() === 'cli') {
            $this->app['clientlogin.db.schema']->build();
        }

        // Controller routes
        $base = isset($this->config['uris']['base']) ? $this->config['uris']['base'] : 'authenticate';
        $this->app->mount('/' . $base, new Controller\ClientLoginController());

        // Scheduled cron listener
        $this->app['dispatcher']->addListener(CronEvents::CRON_DAILY, [$this, 'cronDaily']);
    }

    /**
     * Cron jobs
     *
     * @param \Bolt\Events\CronEvent $event
     */
    public function cronDaily(CronEvent $event)
    {
        $event->output->writeln("<comment>ClientLogin: Clearing old sessions</comment>");
        $this->app['clientlogin.db']->doRemoveExpiredSessions();
    }

    /**
     * Add our Nut commands to the application.
     */
    private function addNutCommands()
    {
        $this->app['nut.commands.add'](new Nut\AccountCreate($this->app));
        $this->app['nut.commands.add'](new Nut\AccountDisable($this->app));
        $this->app['nut.commands.add'](new Nut\AccountEnable($this->app));
        $this->app['nut.commands.add'](new Nut\PasswordReset($this->app));
    }
}
