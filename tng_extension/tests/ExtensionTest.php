<?php

namespace Bolt\Extension\Bolt\ClientLogin\Tests;

use Bolt\Extension\Bolt\ClientLogin\Extension;
use Bolt\Nut\DatabaseRepair;
use Bolt\Tests\BoltUnitTest;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Ensure that ClientLogin loads correctly.
 */
class ExtensionTest extends BoltUnitTest
{
    public function setup()
    {
        $this->resetDb();

        $this->app = $this->getApp();
        $command = new DatabaseRepair($this->app);
        $tester = new CommandTester($command);
        $tester->execute([]);

        $this->extension = new Extension($this->app);
        $this->app['extensions']->register($this->extension);
        $this->name = $this->extension->getName();
        $this->app['extensions']->initialize();
    }

    public function testExtensionRegister()
    {
        // Assert the correct name
        $this->assertSame($this->name, 'ClientLogin');
        $this->assertSame($this->extension, $this->app['extensions.' . $this->name]);

        // Assert config
        $this->assertArrayHasKey('providers',     $this->extension->config);
        $this->assertArrayHasKey('basepath',      $this->extension->config);
        $this->assertArrayHasKey('template',      $this->extension->config);
        $this->assertArrayHasKey('zocial',        $this->extension->config);
        $this->assertArrayHasKey('login_expiry',  $this->extension->config);
        $this->assertArrayHasKey('debug',         $this->extension->config);
        $this->assertArrayHasKey('response_noun', $this->extension->config);
    }
}
