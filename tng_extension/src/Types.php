<?php

namespace Bolt\Extension\Bolt\ClientLogin;

/**
 * ClientLogin type constants.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Types
{
    const AUTH_PASSWORD = 'password';
    const AUTH_OAUTH1 = 'oauth1';
    const AUTH_OAUTH2 = 'oauth2';

    const FORM_NAME_PASSWORD = 'clientlogin_password';

    /**
     * We are the Singleton. You will be assimilated. Resistance is futile.
     */
    private function __construct()
    {
    }
}
