<?php

namespace Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler;

use Bolt\Application;
use Bolt\Extension\Bolt\ClientLogin\Authorisation\CookieManager;
use Bolt\Extension\Bolt\ClientLogin\Authorisation\SessionToken;
use Bolt\Extension\Bolt\ClientLogin\Authorisation\TokenManager;
use Bolt\Extension\Bolt\ClientLogin\Config;
use Bolt\Extension\Bolt\ClientLogin\Database\RecordManager;
use Bolt\Extension\Bolt\ClientLogin\Event\ClientLoginEvent;
use Bolt\Extension\Bolt\ClientLogin\Exception;
use Bolt\Extension\Bolt\ClientLogin\OAuth2\ResourceServer\ProviderManager;
use Bolt\Extension\Bolt\ClientLogin\Response\SuccessRedirectResponse;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Authorisation control class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class HandlerBase
{
    /** @var \Bolt\Application */
    protected $app;
    /** @var \Symfony\Component\HttpFoundation\Request */
    protected $request;

    /** @var \Bolt\Extension\Bolt\ClientLogin\Config */
    private $config;
    /** @var TokenManager */
    private $tm;

    /**
     * @param Application $app
     */
    public function __construct(Application $app, RequestStack $requestStack)
    {
        if (!$this->request = $requestStack->getCurrentRequest()) {
            throw new Exception\ConfigurationException(sprintf('%s can not be instated outside of the request cycle.', get_class($this)));
        }

        $this->app    = $app;
        $this->config = $app['clientlogin.config'];
        $this->tm     = $app['clientlogin.manager.token'];
    }

    /**
     * Check the login.
     *
     * @throws Exception\DisabledProviderException
     *
     * @return Response|null
     */
    protected function login()
    {
        $providerName = $this->getProviderManager()->getProviderName();
        $provider = $this->getConfig()->getProvider($providerName);

        if ($provider['enabled'] !== true) {
            throw new Exception\DisabledProviderException('Invalid provider setting.');
        }

        if ($this->app['clientlogin.session']->isLoggedIn($this->request)) {
            return new SuccessRedirectResponse('/');
        }

        // Set user feedback messages
        $this->app['clientlogin.feedback']->set('message', 'Login was route complete, redirecting for authentication.');
    }

    /**
     * Logout a profile.
     *
     * @return Response
     */
    protected function logout()
    {
        if ($this->app['clientlogin.session']->isLoggedIn($this->request)) {
            $this->getTokenManager()->removeToken(TokenManager::TOKEN_ACCESS);
            $this->app['clientlogin.feedback']->set('message', 'Logout was successful.');
        }

        $cookiePaths = $this->getConfig()->getCookiePaths();
        $response = new SuccessRedirectResponse('/');
        CookieManager::clearResponseCookies($response, $cookiePaths);

        return $response;
    }

    /**
     * Proceess a profile login validation attempt.
     *
     * @return Response
     */
    protected function process($grantType)
    {
        $accessToken = $this->getAccessToken($this->request, $grantType);
        $guid = $this->handleAccountTransition($accessToken);

        // Update the PHP session
        $this->getTokenManager()->setAuthToken($guid, $accessToken);

        // Fetch the newly set session token and dispatch the event
        $sessionToken = $this->getTokenManager()->getToken(TokenManager::TOKEN_ACCESS);
//$this->dispatchEvent(ClientLoginEvent::LOGIN_POST, $sessionToken);

        $response = new SuccessRedirectResponse('/');
        $cookiePaths = $this->getConfig()->getCookiePaths();
        CookieManager::setResponseCookies($response, $accessToken, $cookiePaths);

        return $response;
    }

    /**
     * Handle a successful account authentication.
     *
     * @param AccessToken $accessToken
     *
     * @throws Exception\RecordHandlerException
     *
     * @return string
     */
    protected function handleAccountTransition(AccessToken $accessToken)
    {
        $providerName = $this->getProviderManager()->getProviderName();
        $resourceOwner = $this->getResourceOwner($accessToken);

        $profile = $this->getRecordManager()->getProfileByResourceOwnerId($providerName, $resourceOwner->getId());
        if ($profile === false) {
            $this->setDebugMessage(sprintf('No profile found for %s ID %s', $providerName, $resourceOwner->getId()));
            $this->getRecordManager()->insertProvider(null, $providerName, $accessToken, $resourceOwner);

            // Now re-fetch the profile for provider record
            $profile = $this->getRecordManager()->getProfileByResourceOwnerId($providerName, $resourceOwner->getId());
            if ($profile === false) {
                throw new Exception\RecordHandlerException('Unable to re-fetch newly created profile.');
            }

            $guid = $this->getValidGuid($profile);
            $account = $this->getRecordManager()->getAccountByGuid($guid);
            if ($account === false) {
                $this->setDebugMessage(sprintf('No account found for GUID %s', $guid));

                // Create the account record with a matching GUID
                $result = $this->getRecordManager()->insertAccount($guid, null, null, null, true);
                if ($result === false) {
                    throw new Exception\RecordHandlerException('Unable to re-fetch newly created account.');
                }
            }
        } else {
            $guid = $this->getValidGuid($profile);
            $this->setDebugMessage(sprintf('Profile found for %s ID %s', $providerName, $resourceOwner->getId()));
            // Update the provider record
            $this->getRecordManager()->updateProvider($guid, $providerName, $resourceOwner);
        }

        // Update the session token record
        $this->setDebugMessage(sprintf('Writing session token for %s ID %s', $providerName, $resourceOwner->getId()));
        $this->getRecordManager()->writeSession($guid, $accessToken);

        return $guid;
    }

    /**
     * Check that a GUID we've been given is valid.
     *
     * @param array $record
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function getValidGuid($record)
    {
        if (!isset($record['guid']) || strlen($record['guid']) !== 36) {
            throw new \RuntimeException('Invalid GUID value being used!');
        }

        return $record['guid'];
    }

    /**
     * Query the provider for the resrouce owner.
     *
     * @param AccessToken $accessToken
     *
     * @throws IdentityProviderException
     *
     * @return ResourceOwnerInterface
     */
    protected function getResourceOwner(AccessToken $accessToken)
    {
        return $this->getProvider()->getResourceOwner($accessToken);
    }

    /**
     * Get the config DI.
     *
     * @return Config
     */
    protected function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the RecordManager DI.
     *
     * @return RecordManager
     */
    protected function getRecordManager()
    {
        return $this->app['clientlogin.records'];
    }

    /**
     * Get the token manager instance.
     *
     * @return TokenManager
     */
    protected function getTokenManager()
    {
        return $this->tm;
    }

    /**
     * Get a provider class object for the request.
     *
     * @throws Exception\InvalidProviderException
     *
     * @return AbstractProvider
     */
    protected function getProvider()
    {
        return $this->app['clientlogin.provider'];
    }

    /**
     * Get the provider manager.
     *
     * @return ProviderManager
     */
    protected function getProviderManager()
    {
        return $this->app['clientlogin.provider.manager'];
    }

    /**
     * Get an access token from the OAuth provider.
     *
     * @param Request $request
     * @param string  $grantType One of the following:
     *                           - 'authorization_code'
     *                           - 'password'
     *                           - 'refresh_token'
     *
     * @throws IdentityProviderException
     * @throws Exception\InvalidAuthorisationRequestException
     *
     * @return AccessToken
     */
    protected function getAccessToken(Request $request, $grantType)
    {
        $code = $request->query->get('code');

        if ($code === null) {
            $this->setDebugMessage('Attempt to get an OAuth2 acess token with an empty code in the request.');

            throw new Exception\InvalidAuthorisationRequestException('No provider access code.');
        }
        $options = ['code' => $code];

        // Try to get an access token using the authorization code grant.
        $accessToken = $this->getProvider()->getAccessToken($grantType, $options);
        $this->setDebugMessage('OAuth token received: ' . json_encode($accessToken));

        return $accessToken;
    }

    /**
     * Write a debug message to both the debug log and the feedback array.
     *
     * @param string $message
     */
    protected function setDebugMessage($message)
    {
        $this->app['logger.system']->debug('[ClientLogin][Handler]: ' . $message, ['event' => 'extensions']);
        $this->app['clientlogin.feedback']->set('debug', $message);
    }

    /**
     * Dispatch event to any listeners.
     *
     * @param string       $type         Either ClientLoginEvent::LOGIN_POST' or ClientLoginEvent::LOGOUT_POST
     * @param SessionToken $sessionToken
     */
    protected function dispatchEvent($type, SessionToken $sessionToken)
    {
        if ($this->app['dispatcher']->hasListeners($type)) {
            $event = new ClientLoginEvent($sessionToken);

            try {
                $this->app['dispatcher']->dispatch($type, $event);
            } catch (\Exception $e) {
                if ($this->getConfig()->get('debug_mode')) {
                    dump($e);
                }

                $this->app['logger.system']->critical('ClientLogin event dispatcher had an error', ['event' => 'exception', 'exception' => $e]);
            }
        }
    }
}
