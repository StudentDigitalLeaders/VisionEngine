<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\ResourceServer\Provider;

use League\OAuth2\Client\Provider\LinkedIn as LeagueLinkedIn;
use League\OAuth2\Client\Provider\LinkedInResourceOwner;
use League\OAuth2\Client\Token\AccessToken;

/**
 * LinkedIn provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class LinkedIn extends LeagueLinkedIn
{
    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new LinkedInResourceOwner($response);
    }
}
