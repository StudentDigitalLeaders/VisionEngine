<?php

namespace Bolt\Extension\Bolt\ClientLogin\Tests;

use Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler\Remote;
use Bolt\Extension\Bolt\ClientLogin\Extension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Remote authentication handler class tests
 *
 * @coversDefaultClass \Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler\Remote
 */
class RemoteLogoutTest extends AbstractHandlerUnitTest
{
    /**
     * @covers ::logout
     */
    public function testLogout()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $app = $this->getApp();
        $extension = new Extension($app);
        $app['extensions']->register($extension);
        $app['extensions']->initialize();

        $request = Request::create('/authenticate/login');
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $app['request'] = $request;
        $app['request_stack'] = $requestStack;

        $base = new Remote($app, $app['request_stack']);
        $response = $base->logout('/gum-tree/koala');

        $this->assertSame('/gum-tree/koala', $response->getTargetUrl());
        $this->assertSame(302, $response->getStatusCode());

        $this->assertTrue($response->isRedirect('/gum-tree/koala'));
    }
}
