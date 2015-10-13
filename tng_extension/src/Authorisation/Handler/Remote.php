<?php

namespace Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler;

use Bolt\Extension\Bolt\ClientLogin\Database;
use Bolt\Extension\Bolt\ClientLogin\Exception;
use Bolt\Extension\Bolt\ClientLogin\Profile;
use Bolt\Extension\Bolt\ClientLogin\Response\SuccessRedirectResponse;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * OAuth login provider.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Remote extends HandlerBase implements HandlerInterface
{
    /** @var AccessToken */
    protected $accessToken;
    /** @var ResourceOwnerInterface */
    protected $resourceOwner;

    /**
     * {@inheritdoc}
     */
    public function login()
    {
        $response = parent::login();
        if ($response instanceof Response) {
            // User is logged in already, from whence they came return them now.
            return $response;
        }

        $response = $this->getAuthorisationRedirectResponse();
        if ($response instanceof Response) {
            return $response;
        }

        throw new \RuntimeException('An error occured with the provider redirect handling.');
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        return parent::process('authorization_code');
    }

    /**
     * {@inheritdoc}
     */
    public function logout()
    {
        return parent::logout();
    }

/*
    protected function getOauthResourceOwner(Request $request)
    {
        if ($cookie = $request->cookies->get(Types::TOKEN_COOKIE_NAME)) {
            $profile = $this->getRecordManager()->getProfileByAccessToken($cookie);

            if (!$profile) {
                throw new Exception\AccessDeniedException('No matching profile found.');
            } elseif (!$profile['enabled']) {
                throw new Exception\AccessDeniedException('Profile disabled.');
            }

            // Compile the options from the database record.
            $options = [
                'resource_owner_id' => $profile['resource_owner_id'],
                'refresh_token'     => $profile['refresh_token'],
                'access_token'      => $profile['access_token'],
                'expires'           => $profile['expires'],
            ];

            // Create and refresh the token
            $accessToken = $this->getRefreshToken(new AccessToken($options));
            $resourceOwner = $this->getProvider()->getResourceOwner($accessToken);

            // Save the new token data
            $providerName = $this->getProviderManager()->getProviderName();
            $this->getRecordManager()->updateProfile($providerName, $accessToken, $resourceOwner);
        }
    }
*/

    /**
     * Create a redirect response to fetch an authorisation code.
     *
     * @param string $approvalPrompt
     *
     * @return RedirectResponse
     */
    protected function getAuthorisationRedirectResponse($approvalPrompt = 'auto')
    {
        $provider = $this->getProvider();
        $providerName = $this->getProviderManager()->getProviderName();

        if ($providerName === 'Google' && $approvalPrompt == 'force') {
            $provider->setAccessType('offline');
        }

        $providerOptions = $this->getProviderManager()->getProviderOptions($providerName);
        $options = array_merge($providerOptions, ['approval_prompt' => $approvalPrompt]);
        $authorizationUrl = $provider->getAuthorizationUrl($options);

        // Get the state generated and store it to the session.
        $this->getTokenManager()->setStateToken($provider->getState());
        $this->setDebugMessage('Storing state token: ' . $provider->getState());

        return new SuccessRedirectResponse($authorizationUrl);
    }

    /**
     * Get a refresh token from the OAuth provider.
     *
     * @param AccessToken $accessToken
     *
     * @throws IdentityProviderException
     *
     * @return AccessToken
     */
    protected function getRefreshToken(AccessToken $accessToken)
    {
        if ($accessToken->hasExpired()) {
            // Try to get an access token using the authorization code grant.
            $accessToken = $this->getProvider()->getAccessToken('refresh_token', ['refresh_token' => $accessToken->getRefreshToken()]);
        }

        return $accessToken;
    }
}
