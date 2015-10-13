<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\ResourceServer;

use Bolt\Extension\Bolt\ClientLogin\Config;
use Bolt\Extension\Bolt\ClientLogin\Exception;
use GuzzleHttp\Client;
use League\OAuth2\Client\Provider\AbstractProvider;
use Psr\Log\LoggerInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provider object management class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ProviderManager
{
    /** @var Config */
    protected $config;
    /** @var \GuzzleHttp\Client */
    protected $guzzleClient;
    /** @var \Psr\Log\LoggerInterface */
    protected $logger;
    /** @var string */
    protected $rootUrl;
    /** @var AbstractProvider */
    protected $provider;
    /** @var string */
    protected $providerName;

    /**
     * Constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config, Client $guzzleClient, LoggerInterface $logger, $rootUrl)
    {
        $this->config = $config;
        $this->guzzleClient = $guzzleClient;
        $this->logger = $logger;
        $this->rootUrl = $rootUrl;
    }

    /**
     * Set the provider for this request.
     *
     * @param Application $app
     * @param Request     $request
     *
     * @throws Exception\InvalidProviderException
     */
    public function setProvider(Application $app, Request $request)
    {
        // Set the provider name that we're using for this request
        $this->setProviderName($request);

        $providerName = $this->getProviderName();
        $providerKey = 'clientlogin.provider.' . strtolower($providerName);

        $app['clientlogin.provider'] = $app->share(
            function ($app) use ($providerKey) {
                return $app[$providerKey]([]);
            }
        );

        $app['logger.system']->debug('[ClientLogin][Provider]: Created provider name: ' . $providerName, ['event' => 'extensions']);

        $this->setProviderHandler($app);
    }

    /**
     * Get a provider class object.
     *
     * @param string $providerName
     *
     * @throws Exception\InvalidProviderException
     *
     * @return AbstractProvider
     */
    public function getProvider($providerName)
    {
        $this->logger->debug('[ClientLogin][Provider]: Fetching provider object: ' . $providerName);

        /** @var \League\OAuth2\Client\Provider\AbstractProvider $providerClass */
        $providerClass = '\\Bolt\\Extension\\Bolt\\ClientLogin\\OAuth2\\Provider\\' . $providerName;

        if (!class_exists($providerClass)) {
            throw new Exception\InvalidProviderException(Exception\InvalidProviderException::INVALID_PROVIDER);
        }

        $options = $this->getProviderOptions($providerName);
        $collaborators = ['httpClient' => $this->guzzleClient];

        return new $providerClass($options, $collaborators);
    }

    /**
     * Get a corrected provider name for the request.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function getProviderName()
    {
        // If the provider name is set, we assume this is called post ->before()
        if ($this->providerName !== null) {
            return $this->providerName;
        }

        // If we have no provider name set, and no valid request, we're out of
        // cycle… and that's like bad… 'n stuff
        throw new \RuntimeException('Attempting to get provider name outside of the request cycle.');
    }

    /**
     * Set a corrected provider name from a request object.
     *
     * @param Request $request
     *
     * @throws \RuntimeException
     */
    protected function setProviderName(Request $request = null)
    {
        if ($request === null) {
            throw new \RuntimeException('Attempting to set provider name outside of the request cycle.');
        }
        $provider = $request->query->get('provider', 'Generic');

        $this->providerName = ucwords(strtolower($provider));
    }

    /**
     * Get a provider config for passing to the library.
     *
     * @param string $providerName
     *
     * @throws Exception\ConfigurationException
     *
     * @return array
     */
    public function getProviderOptions($providerName)
    {
        $providerConfig = $this->config->getProvider($providerName);

        if (empty($providerConfig['clientId'])) {
            throw new Exception\ConfigurationException('Provider client ID required: ' . $providerName);
        }
        if (empty($providerConfig['clientSecret'])) {
            throw new Exception\ConfigurationException('Provider secret key required: ' . $providerName);
        }
        if (empty($providerConfig['scopes'])) {
            throw new Exception\ConfigurationException('Provider scope(s) required: ' . $providerName);
        }

        $options = [
            'clientId'     => $providerConfig['clientId'],
            'clientSecret' => $providerConfig['clientSecret'],
            'scope'        => $providerConfig['scopes'],
            'redirectUri'  => $this->getCallbackUrl($providerName),
        ];

        if ($providerName === 'Local') {
            $base = $this->config->getUrlRoot() . $this->config->getUriBase() . '/';
            $options['urlAuthorize'] = $base . $this->config->getUriAuthorise();
            $options['urlAccessToken'] = $base . $this->config->getUriAccessToken();
            $options['urlResourceOwnerDetails'] = $base . $this->config->getUriResourceOwnerDetails();
        }

        return $options;
    }

    /**
     * Get the Authorisation\AuthorisationInterface class to handle the request.
     *
     * @param \Silex\Application $app
     *
     * @throws InvalidAuthorisationRequestException
     */
    protected function setProviderHandler(Application $app)
    {
        $providerName = $this->getProviderName();
        if ($providerName === null) {
            $app['logger.system']->debug('[ClientLogin][Provider]: Request was missing a provider in the GET.', ['event' => 'extensions']);
            throw new Exception\InvalidAuthorisationRequestException('Authentication configuration error. Unable to proceed!');
        }

        $providerConfig = $this->config->getProvider($providerName);
        if ($providerConfig === null) {
            $app['logger.system']->debug('[ClientLogin][Provider]: Request provider did not match any configured providers.', ['event' => 'extensions']);
            throw new Exception\InvalidAuthorisationRequestException('Authentication configuration error. Unable to proceed!');
        }

        if ($providerConfig['enabled'] !== true && $providerName !== 'Generic') {
            $app['logger.system']->debug('[ClientLogin][Provider]: Request provider was disabled.', ['event' => 'extensions']);
            throw new Exception\InvalidAuthorisationRequestException('Authentication configuration error. Unable to proceed!');
        }

        if ($providerName === 'Local' && !isset($app['boltforms'])) {
            throw new \RuntimeException('Local handler requires BoltForms (v2.5.0 or later preferred).');
        }

        $handlerKey = $this->getHandlerKey($providerName);
        $app['clientlogin.handler'] = $app->share(
            function ($app) use ($app, $handlerKey) {
                return $app[$handlerKey]([]);
            }
        );

        $this->provider = $app['clientlogin.handler'];
    }

    /**
     * Get the service key for our provider.
     *
     * @param string $providerName
     *
     * @return string
     */
    protected function getHandlerKey($providerName)
    {
        if ($providerName === 'Local') {
            return 'clientlogin.handler.local';
        }
        return 'clientlogin.handler.remote';
    }

    /**
     * Construct the authorisation URL with query parameters.
     *
     * @param string $providerName
     *
     * @return string
     */
    protected function getCallbackUrl($providerName)
    {
        $key = $this->config->get('response_noun');
        $url = $this->rootUrl . $this->config->getUriBase() . "/oauth2/callback?$key=$providerName";
        $this->logger->debug("[ClientLogin][Provider]: Setting callback URL: $url");

        return $url;
    }
}
