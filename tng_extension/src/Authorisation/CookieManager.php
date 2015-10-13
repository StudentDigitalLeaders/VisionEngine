<?php

namespace Bolt\Extension\Bolt\ClientLogin\Authorisation;

use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\Cookie as CookieBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cookie manager class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class CookieManager
{
    /**
     * Create an authentication cookie.
     *
     * @param string      $path
     * @param AccessToken $accessToken
     *
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public static function create($path, AccessToken $accessToken)
    {
        if (!$expire = $accessToken->getExpires()) {
            $expire = time() + 3600;
        }

        return new CookieBase(TokenManager::TOKEN_COOKIE_NAME, $accessToken->getToken(), $expire, $path);
    }

    /**
     * Given a response objects, add them by paths.
     *
     * @param Response    $response
     * @param AccessToken $accessToken
     * @param array       $cookiePaths
     */
    public static function setResponseCookies(Response $response, AccessToken $accessToken, array $cookiePaths)
    {
        foreach ($cookiePaths as $cookiePath) {
            $cookie = self::create($cookiePath, $accessToken);
            $response->headers->setCookie($cookie);
        }
    }

    /**
     * Have the response clear browser cookies for given paths.
     *
     * @param Response $response
     * @param array    $cookiePaths
     */
    public static function clearResponseCookies(Response $response, array $cookiePaths)
    {
        foreach ($cookiePaths as $cookiePath) {
            $response->headers->clearCookie(TokenManager::TOKEN_COOKIE_NAME, $cookiePath);
        }
    }
}
