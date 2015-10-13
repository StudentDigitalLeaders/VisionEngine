<?php

namespace Bolt\Extension\Bolt\ClientLogin\Authorisation\AccessToken;

use League\OAuth2\Client\Token\AccessToken;

/**
 * .
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class LocalAccess extends AccessToken
{
    /**
     * Constructor.
     */
    public function __construct(array $options)
    {
        parent::__construct($options);
    }
}
