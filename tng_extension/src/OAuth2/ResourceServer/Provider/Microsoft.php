<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\ResourceServer\Provider;

use League\OAuth2\Client\Token\AccessToken;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft as LeagueMicrosoft;
use Stevenmaguire\OAuth2\Client\Provider\MicrosoftResourceOwner;

/**
 * Microsoft provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Microsoft extends LeagueMicrosoft
{
    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new MicrosoftResourceOwner($response);
    }
}
