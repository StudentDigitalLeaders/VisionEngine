<?php

namespace Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler;

use Bolt\Extension\Bolt\ClientLogin\Exception\DisabledProviderException;
use Bolt\Extension\Bolt\ClientLogin\Exception\InvalidAuthorisationRequestException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentication provider interface.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
interface HandlerInterface
{
    /**
     * Login a client.
     *
     * @throws DisabledProviderException
     * @throws InvalidAuthorisationRequestException
     *
     * @return Response
     */
    public function login();

    /**
     * Process a client login attempt.
     *
     * @return Response
     */
    public function process();

    /**
     * Logout a client.
     *
     * @return Response
     */
    public function logout();
}
