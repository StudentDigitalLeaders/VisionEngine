<?php

namespace Bolt\Extension\Bolt\ClientLogin\Event;

use Bolt\Extension\Bolt\ClientLogin\Authorisation\SessionToken;
use Symfony\Component\EventDispatcher\Event;

class ClientLoginEvent extends Event
{
    const LOGIN_POST = 'clientlogin.Login';
    const LOGOUT_POST = 'clientlogin.Logout';

    /** @var SessionToken */
    private $sessionToken;

    /**
     * @param SessionToken $sessionToken
     */
    public function __construct(SessionToken $sessionToken)
    {
        $this->sessionToken = $sessionToken;
    }

    /**
     * Return the SessionToken
     */
    public function getSessionToken()
    {
        return $this->sessionToken;
    }
}
