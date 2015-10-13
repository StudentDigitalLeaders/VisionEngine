<?php

namespace Bolt\Extension\Bolt\ClientLogin\Provider;

use Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler;
use Bolt\Extension\Bolt\ClientLogin\Authorisation\SessionManager;
use Bolt\Extension\Bolt\ClientLogin\Config;
use Bolt\Extension\Bolt\ClientLogin\Database\RecordManager;
use Bolt\Extension\Bolt\ClientLogin\Database\Schema;
use Bolt\Extension\Bolt\ClientLogin\Exception;
use Bolt\Extension\Bolt\ClientLogin\Feedback;
use Bolt\Extension\Bolt\ClientLogin\OAuth2\ResourceServer\Provider;
use Bolt\Extension\Bolt\ClientLogin\OAuth2\ResourceServer\ProviderManager;
use Bolt\Extension\Bolt\ClientLogin\Twig\Helper\UserInterface;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Bolt\Extension\Bolt\ClientLogin\Authorisation\TokenManager;
use Bolt\Extension\Bolt\ClientLogin\OAuth2\Service\Server;

class ServiceProvider implements ServiceProviderInterface
{
    /** @var array */
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function boot(Application $app)
    {
    }

    public function register(Application $app)
    {
        $this->registerServicesBase($app);

        $this->registerServicesSession($app);

        $this->registerServicesDatabase($app);

        $this->registerServicesHandlers($app);

        $this->registerServicesProviders($app);

        $this->registerServicesServer($app);

        $this->registerServicesDeprecated($app);
    }

    protected function registerServicesBase(Application $app)
    {
        // Configuration service
        $app['clientlogin.config'] = $app->share(
            function ($this) use ($app) {
                $rooturl = $app['resources']->getUrl('rooturl');

                return new Config($this->config, $rooturl);
            }
        );

        // Twig user interface service
        $app['clientlogin.ui'] = $app->share(
            function ($app) {
                return new UserInterface($app);
            }
        );

        // Feedback message handling service
        $app['clientlogin.feedback'] = $app->share(
            function ($app) {
                $feedback = new Feedback($app['session']);
                $app->after([$feedback, 'after']);

                return $feedback;
            }
        );
    }

    protected function registerServicesSession(Application $app)
    {
        // Authenticated session handling service
        $app['clientlogin.session'] = $app->share(
            function ($app) {
                return new SessionManager(
                    $app['clientlogin.records'],
                    $app['session'],
                    $app['request_stack'],
                    $app['logger.system']
                );
            }
        );

        // Token manager service
        $app['clientlogin.manager.token'] = $app->share(
            function ($app) {
                return new TokenManager($app['session'], $app['randomgenerator'], $app['logger.system']);
            }
        );
    }

    protected function registerServicesDatabase(Application $app)
    {
        $tablePrefix = rtrim($app['config']->get('general/database/prefix', 'bolt_'), '_') . '_';
        $app['clientlogin.db.table'] = $tablePrefix . 'clientlogin';

        // Database record handling service
        $app['clientlogin.records'] = $app->share(
            function ($app) {
                $records = new RecordManager(
                    $app['db'],
                    $app['clientlogin.config'],
                    $app['logger.system'],
                    $app['clientlogin.db.table']
                );

                return $records;
            }
        );

        // Schema for ClientLogin tables
        $app['clientlogin.db.schema'] = $app->share(
            function ($app) {
                $schema = new Schema(
                    $app['integritychecker'],
                    $app['clientlogin.db.table']
                );

                return $schema;
            }
        );
    }

    protected function registerServicesServer(Application $app)
    {
        // Local OAuth2 server service
        $app['clientlogin.server'] = $app->share(
            function ($app) {
                return new Server($app);
            }
        );
    }

    protected function registerServicesHandlers(Application $app)
    {
        // Authentication handler service. Will be chosen, and set, inside a request cycle
        $app['clientlogin.handler'] = $app->share(
            function () {
                throw new \RuntimeException('ClientLogin authentication handler not set up!');
            }
        );

        // Handler object for local authentication processing
        $app['clientlogin.handler.local'] = $app->protect(
            function ($app) use ($app) {
                return new Handler\Local($app, $app['request_stack']);
            }
        );

        // Handler object for remote authentication processing
        $app['clientlogin.handler.remote'] = $app->protect(
            function ($app) use ($app) {
                return new Handler\Remote($app, $app['request_stack']);
            }
        );
    }

    protected function registerServicesProviders(Application $app)
    {
        // Provider manager
        $app['clientlogin.provider.manager'] = $app->share(
            function ($app) {
                $rootUrl = $app['resources']->getUrl('rooturl');

                return new ProviderManager($app['clientlogin.config'], $app['clientlogin.guzzle'], $app['logger.system'], $rootUrl);
            }
        );

        // OAuth provider service. Will be chosen, and set, inside a request cycle
        $app['clientlogin.provider'] = $app->share(
            function () {
                throw new \RuntimeException('ClientLogin authentication provider not set up!');
            }
        );

        // Generic OAuth provider object
        $app['clientlogin.provider.generic'] = $app->protect(
            function () {
                return new Provider\Generic([]);
            }
        );

        // Provider objects for each enabled provider
        foreach ($this->config['providers'] as $providerName => $providerConfig) {
            if ($providerConfig['enabled'] === true) {
                $app['clientlogin.provider.' . strtolower($providerName)] = $app->protect(
                    function ($app) use ($app, $providerName) {
                        return $app['clientlogin.provider.manager']->getProvider($providerName);
                    }
                );
            }
        }
    }

    /**
     * @internal Temporary workaround until Bolt core can update to Guzzle 6.
     * @deprecated Since 3.0 and will be removed for Bolt v3
     *
     * NOTE:
     * This uses a custom autoloader injected by the activation of the
     * $app['clientlogin.guzzle.loader'] Pimple.
     *
     * @param Application $app
     */
    protected function registerServicesDeprecated(Application $app)
    {
        $app['clientlogin.guzzle'] = $app->share(
            function ($app) {
                // We're needed, pop the pimple.
                $app['clientlogin.guzzle.loader'] = $app['clientlogin.guzzle.loader'];

                return new \GuzzleHttp\Client();
            }
        );

        $app['clientlogin.guzzle.loader'] = $app->share(
            function () {
                $baseDir = dirname(dirname(__DIR__));

                require $baseDir . '/lib/GuzzleHttp/Guzzle/functions_include.php';
                require $baseDir . '/lib/GuzzleHttp/Promise/functions_include.php';
                require $baseDir . '/lib/GuzzleHttp/Psr7/functions_include.php';

                $loader = new \Composer\Autoload\ClassLoader();
                $loader->setPsr4('GuzzleHttp\\', [
                    $baseDir . '/lib/GuzzleHttp/Guzzle',
                    $baseDir . '/lib/GuzzleHttp/Promise',
                    $baseDir . '/lib/GuzzleHttp/Psr7',
                ]);
                $loader->setPsr4('GuzzleHttp\\Promise\\', [
                    $baseDir . '/lib/GuzzleHttp/Promise',
                ]);
                $loader->setPsr4('GuzzleHttp\\Psr7\\', [
                    $baseDir . '/lib/GuzzleHttp/Psr7',
                ]);
                $loader->register(true);
            }
        );
    }
}
