<?php

namespace Bolt\Extension\Bolt\ClientLogin\Provider\Tests;

use Bolt\Extension\Bolt\ClientLogin\Authorisation\Session;
use Bolt\Extension\Bolt\ClientLogin\Extension;
use Bolt\Extension\Bolt\ClientLogin\Provider\ServiceProvider;
use Bolt\Tests\BoltUnitTest;

/**
 * ClientLogin Service Provider tests
 */
class ClientLoginServiceProviderTest extends BoltUnitTest
{
    public function testProvider()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $app = $this->getApp();
        $provider = new ServiceProvider($app);
        $app->register($provider);
        $extension = new Extension($app);
        $app['extensions']->register($extension);
        $provider->boot($app);

        $this->assertNotEmpty($app['clientlogin.session']);
//         $this->assertNotEmpty($app['clientlogin.handler.local']);
//         $this->assertNotEmpty($app['clientlogin.handler.remote']);
        $this->assertNotEmpty($app['clientlogin.records']);
        $this->assertNotEmpty($app['clientlogin.db.schema']);
        $this->assertNotEmpty($app['clientlogin.config']);

        $this->assertInstanceOf('Bolt\Extension\Bolt\ClientLogin\Authorisation\Session', $app['clientlogin.session']);
//         $this->assertInstanceOf('Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler\Local', $app['clientlogin.handler.local']);
//         $this->assertInstanceOf('Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler\Remote', $app['clientlogin.handler.remote']);
        $this->assertInstanceOf('Bolt\Extension\Bolt\ClientLogin\Database\RecordManager', $app['clientlogin.records']);
        $this->assertInstanceOf('Bolt\Extension\Bolt\ClientLogin\Database\Schema', $app['clientlogin.db.schema']);
        $this->assertInstanceOf('Bolt\Extension\Bolt\ClientLogin\Config', $app['clientlogin.config']);

        $app->boot();
    }
}
