<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\ResourceServer\Provider;

use League\OAuth2\Client\Provider\Facebook as LeagueFacebook;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Facebook provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Facebook extends LeagueFacebook
{
    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new FacebookResourceOwner($response);
    }
}
