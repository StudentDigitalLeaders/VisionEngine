<?php

namespace Bolt\Extension\Bolt\ClientLogin\Controller\Tests;

use Bolt\Extension\Bolt\ClientLogin\Extension;
use Bolt\Tests\BoltUnitTest;
use Symfony\Component\HttpFoundation\Request;

/**
 * ClientLoginController tests
 */
class ClientLoginControllerTest extends BoltUnitTest
{
    public function setup()
    {
        $this->app = $this->getApp();
        $this->extension = new Extension($this->app);
        $this->app['extensions']->register($this->extension);
        $this->name = $this->extension->getName();
        $this->app['extensions']->initialize();
    }

    public function testAuthenticationLogin()
    {
        //
    }

    public function testAuthenticationLogout()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $app = $this->getApp();
        $twig = $this->getMockTwig();

        $request = Request::create('/authenticate/logout', 'GET', array('redirect' => '/'));

        $app->run($request);
    }

    public function testAuthenticationEndpoint()
    {
        //
    }
}
