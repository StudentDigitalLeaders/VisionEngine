<?php

namespace Bolt\Extension\Bolt\ClientLogin\Tests;

use Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler\Remote;
use Bolt\Extension\Bolt\ClientLogin\Extension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Remote authentication handler class tests
 */
class HandlerBaseTest extends AbstractHandlerUnitTest
{

    protected function getApp($boot = true, $routeAppend = '')
    {
        $app = parent::getApp($boot);
        $extension = new Extension($app);
        $app['extensions']->register($extension);
        $app['extensions']->initialize();

        $request = Request::create('/authenticate' .  $routeAppend);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $app['request'] = $request;
        $app['request_stack'] = $requestStack;
        $app['clientlogin.provider.manager']->setProvider($app, $request);

        return $app;
    }

    public function testHandlerBase()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $app = $this->getApp(true, '/login?provider=Generic');
//         $this->assertInstanceOf('\Foo', $app['clientlogin.handler']);
    }

    /**
     * @ expectedException \Bolt\Extension\Bolt\ClientLogin\Exception\InvalidAuthorisationRequestException
     * @ expectedExceptionMessage No provider access code.
     */
    public function testProcessNoAccessCode()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $app = $this->getApp(true, 'endpoint');

        $base = new Remote($app, $app['request_stack']);
        $base->process('/gum-tree/koala');
    }

    /**
     * @ expectedException \Bolt\Extension\Bolt\ClientLogin\Exception\InvalidProviderException
     * @ expectedExceptionMessage Invalid provider.
     *
     * NOTE: This test expect the exception that currently is emitted from
     * Handler Base::getProviderName()
     */
    public function testProcessWithCode()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $app = $this->getApp();
        $extension = new Extension($app);
        $app['extensions']->register($extension);
        $app['extensions']->initialize();

        $request = Request::create('/authenticate/endpoint', 'GET', ['code' => 't00secret']);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $app['request'] = $request;
        $app['request_stack'] = $requestStack;

        $remote = $this->getMockForAbstractClass(
            '\Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler\HandlerBase',
            [$app, $app['request_stack']],
            '',
            true,
            true,
            true,
            ['getAccessToken']
        );
        $remote
            ->expects($this->once())
            ->method('getAccessToken')
        ;
        $app['clientlogin.handler.remote'] = $remote;
        $app['clientlogin.handler.remote']->process('/gum-tree/koala');
    }

    /**
     * @ expectedException \Bolt\Extension\Bolt\ClientLogin\Exception\InvalidProviderException
     * @ expectedExceptionMessage Invalid provider.
     */
    public function testProcessWithCodeProviderInvalid()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );

        $app = $this->getApp();
        $extension = new Extension($app);
        $app['extensions']->register($extension);
        $app['extensions']->initialize();

        $request = Request::create('/authenticate/endpoint', 'GET', ['code' => 't00secret', 'provider' => 'FooBar']);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $app['request'] = $request;
        $app['request_stack'] = $requestStack;

        $remote = $this->getMock(
            '\Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler\Remote',
            ['getAccessToken'],
            [$app, $app['request_stack']]
        );
        $remote
            ->expects($this->once())
            ->method('getAccessToken')
        ;
        $app['clientlogin.handler.remote'] = $remote;
        $app['clientlogin.handler.remote']->process('/gum-tree/koala');
    }

    /**
     * @ expectedException \Bolt\Extension\Bolt\ClientLogin\Exception\InvalidProviderException
     * @ expectedExceptionMessage Invalid provider.
     */
    public function testProcessWithCodeProviderValid()
    {
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
//         $app = $this->getApp();
//         $extension = new Extension($app);
//         $app['extensions']->register($extension);
//         $app['extensions']->initialize();

//         $request = Request::create('/authenticate/endpoint', 'GET', ['code' => 't00secret', 'provider' => 'Generic']);
//         $requestStack = new RequestStack();
//         $requestStack->push($request);
//         $app['request'] = $request;
//         $app['request_stack'] = $requestStack;

//         $app['clientlogin.config']->set();

/*
  "enabled" => false
  "clientId" => null
  "clientSecret" => null
  "scopes" => []

 */

//         $base = new Remote($app, $app['request_stack']);
//         $response = $base->process('/gum-tree/koala');

// dump($response);
    }

//     public function testProcessX()
//     {
//         $app = $this->getApp();
//         $extension = new Extension($app);
//         $app['extensions']->register($extension);
//         $app['extensions']->initialize();

//         $request = Request::create('/authenticate/endpoint');
//         $requestStack = new RequestStack();
//         $requestStack->push($request);
//         $app['request'] = $request;
//         $app['request_stack'] = $requestStack;

//         $base = new Remote($app, $app['request_stack']);
//         $response = $base->process('/gum-tree/koala');

// dump($response);
//     }
}
