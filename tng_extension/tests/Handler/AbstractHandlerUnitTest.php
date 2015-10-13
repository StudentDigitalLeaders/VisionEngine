<?php

namespace Bolt\Extension\Bolt\ClientLogin\Tests;

use Bolt\Application;
use Bolt\Extension\Bolt\ClientLogin\Authorisation\TokenManager;
use Bolt\Extension\Bolt\ClientLogin\Authorisation\Session;
use Bolt\Extension\Bolt\ClientLogin\Authorisation\SessionToken;
use Bolt\Tests\BoltUnitTest;
use League\OAuth2\Client\Token\AccessToken;

/**
 *
 */
abstract class AbstractHandlerUnitTest extends BoltUnitTest
{
    protected function getClientLoginSession(Application $app, $fakeIsLogged = true)
    {
        $mock = $this->getMock(
            '\Bolt\Extension\Bolt\ClientLogin\Authorisation\Session',
            array('isLoggedIn'),
            array($app['clientlogin.records'], $app['session'], $app['request_stack'], $app['logger.system'])
        );
        $mock
            ->expects($this->once())
            ->method('isLoggedIn')
            ->willReturn($this->returnValue($fakeIsLogged))
        ;

        return $mock;
    }

    protected function setLoggedInSession(Application $app)
    {
        $accessToken = new AccessToken([
            'access_token'      => '0verTher3Dad!',
            'resource_owner_id' => '2223097779'
        ]);
        $sessionToken = new SessionToken('fe4687dd-6d5b-44ae-af5e-db0e4c8b407c', $accessToken);
        $app['session']->set(TokenManager::TOKEN_ACCESS, $sessionToken);
    }
}
