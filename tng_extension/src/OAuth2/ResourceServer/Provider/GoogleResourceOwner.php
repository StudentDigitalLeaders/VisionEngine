<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\ResourceServer\Provider;

use League\OAuth2\Client\Provider\GoogleUser as LeagueGoogleResourceOwner;

/**
 * Google ResourceOwner provider extension.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class GoogleResourceOwner extends LeagueGoogleResourceOwner
{
    /**
     * Get avatar image URL.
     *
     * @return string|null
     */
    public function getImageurl()
    {
        if (!empty($this->response['image']['url'])) {
            return $this->response['image']['url'];
        }
    }
}
