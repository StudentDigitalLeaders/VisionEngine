<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\ResourceServer\Provider;

use League\OAuth2\Client\Provider\GenericProvider as LeagueGenericProvider;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Local provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Local extends LeagueGenericProvider
{
    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GenericResourceOwner($response);
    }
}
