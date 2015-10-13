<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\ResourceServer\Provider;

use League\OAuth2\Client\Provider\Github as LeagueGitHub;
use League\OAuth2\Client\Token\AccessToken;

/**
 * GitHub provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class GitHub extends LeagueGitHub
{
    /**
     * {@inheritdoc}
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new GitHubResourceOwner($response);
    }
}
