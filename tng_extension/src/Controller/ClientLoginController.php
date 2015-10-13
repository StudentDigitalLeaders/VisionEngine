<?php

namespace Bolt\Extension\Bolt\ClientLogin\Controller;

use Bolt\Extension\Bolt\ClientLogin\Authorisation\CookieManager;
use Bolt\Extension\Bolt\ClientLogin\Authorisation\Handler;
use Bolt\Extension\Bolt\ClientLogin\Event\ClientLoginExceptionEvent as ExceptionEvent;
use Bolt\Extension\Bolt\ClientLogin\Exception\AccessDeniedException;
use Bolt\Extension\Bolt\ClientLogin\Exception\InvalidAuthorisationRequestException;
use Bolt\Extension\Bolt\ClientLogin\Response\FailureResponse;
use Bolt\Extension\Bolt\ClientLogin\Response\SuccessRedirectResponse;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ClientLogin authentication controller
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ClientLoginController implements ControllerProviderInterface
{
    const FINAL_REDIRECT_KEY = 'bolt.clientlogin.redirect';

    /** @var \Bolt\Extension\Bolt\ClientLogin\Config */
    private $config;

    /**
     * @param \Silex\Application $app
     *
     * @return \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $this->config = $app['clientlogin.config'];

        /** @var $ctr \Silex\ControllerCollection */
        $ctr = $app['controllers_factory']
            ->before([$this, 'before']);

        // Member login
        $ctr->match('/login', [$this, 'authenticationLogin'])
            ->bind('authenticationLogin')
            ->method('GET|POST');

        // Member logout
        $ctr->match('/logout', [$this, 'authenticationLogout'])
            ->bind('authenticationLogout')
            ->method('GET');

        // OAuth callback URI
        $ctr->match('/oauth2/callback', [$this, 'authenticationCallback'])
            ->bind('authenticationCallback')
            ->method('GET|POST');

        // OAuth authorise URI
        $ctr->match('/oauth2/authorise', [$this, 'authenticationAuthorise'])
            ->bind('authenticationAuthorise')
            ->method('GET|POST');

        // Own the rest of the base route
        $ctr->match('/{url}', [$this, 'authenticationDefault'])
            ->bind('authenticationDefault')
            ->method('GET|POST')
            ->assert('url', '.+');

        return $ctr;
    }

    /**
     * Before middleware to:
     * - Add our logging handler during debug mode
     * - Set the request's provider in the provider manager
     *
     * @param Request     $request
     * @param Application $app
     */
    public function before(Request $request, Application $app)
    {
        if ($this->config->isDebug()) {
            $debuglog = $app['resources']->getPath('cache') . '/authenticate.log';
            $app['logger.system']->pushHandler(new StreamHandler($debuglog, Logger::DEBUG));
        }

        // Fetch the request off the stack so we don't get called out of cycle
        $request = $app['request_stack']->getCurrentRequest();
        $app['clientlogin.provider.manager']->setProvider($app, $request);
    }

    /**
     * Login route.
     *
     * @param \Silex\Application $app
     * @param Request            $request
     *
     * @return Response
     */
    public function authenticationLogin(Application $app, Request $request)
    {
        if (!$request->isSecure()) {
            // Log a warning if this route is not HTTPS
            $msg = sprintf("[ClientLogin][Controller]: Login route '%s' is not being served over HTTPS. This is insecure and vulnerable!", $request->getPathInfo());
            $app['logger.system']->critical($msg, ['event' => 'extensions']);
        }
        $this->setFinalRedirectUrl($app, $request);

        $response = $this->getFinalResponse($app, 'login');

        return $response;
    }

    /**
     * Logout route.
     *
     * @param \Silex\Application $app
     * @param Request            $request
     *
     * @return RedirectResponse
     */
    public function authenticationLogout(Application $app, Request $request)
    {
        if (!$app['clientlogin.provider.manager']->getProviderName()) {
            $request->query->set('provider', 'Generic');
        }

        $response = $this->getFinalResponse($app, 'logout');
        if ($response instanceof SuccessRedirectResponse) {
            $response->setTargetUrl($this->getRedirectUrl($app));
        }

        CookieManager::clearResponseCookies($response, $app['clientlogin.config']->getCookiePaths());

        return $response;
    }

    /**
     * Authorisation callback.
     *
     * @param \Silex\Application $app
     * @param Request            $request
     *
     * @return Response
     */
    public function authenticationCallback(Application $app, Request $request)
    {
        $response = $this->getFinalResponse($app, 'process');

        if ($response instanceof SuccessRedirectResponse) {
            $response->setTargetUrl($this->getRedirectUrl($app));
        }

        return $response;
    }

    /**
     * Authorisation endpoint.
     *
     * @param \Silex\Application $app
     * @param Request            $request
     *
     * @return Response
     */
    public function authenticationAuthorise(Application $app, Request $request)
    {
    }

    /**
     * Default route to throw an error on.
     *
     * @param \Silex\Application $app
     * @param Request            $request
     *
     * @return Response
     */
    public function authenticationDefault(Application $app, Request $request)
    {
        $e = new  AccessDeniedException('Invalid route!');

        return $this->getExceptionResponse($app, $e);
    }

    /**
     * Get the required route response.
     *
     * @param Application $app
     * @param string      $action
     *
     * @return Response
     */
    private function getFinalResponse(Application $app, $action)
    {
        try {
            $response = $app['clientlogin.handler']->{$action}();
        } catch (\Exception $e) {
            return $this->getExceptionResponse($app, $e);
        }
// DEBUG:
// Check that our response classes are OK
// $this->isResponseValid($response);

        return $response;
    }

    /**
     * Get an exception state's HTML response page.
     *
     * @param Application $app
     * @param \Exception  $e
     *
     * @return Response
     */
    private function getExceptionResponse(Application $app, \Exception $e)
    {
        if ($e instanceof IdentityProviderException) {
            // Thrown by the OAuth2 library
            $app['clientlogin.feedback']->set('message', 'An exception occurred authenticating with the provider.');
            // 'Access denied!'
            $response = new Response('', Response::HTTP_FORBIDDEN);
        } elseif ($e instanceof InvalidAuthorisationRequestException) {
            // Thrown deliberately internally
            $app['clientlogin.feedback']->set('message', 'An exception occurred authenticating with the provider.');
            // 'Access denied!'
            $response = new Response('', Response::HTTP_FORBIDDEN);
        } else {
            // Yeah, this can't be good…
            $app['clientlogin.feedback']->set('message', 'A server error occurred, we are very sorry and someone has been notified!');
            $response = new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Dispatch an event so that subscribers can extend exception handling
        if ($app['dispatcher']->hasListeners(ExceptionEvent::ERROR)) {
            try {
                $app['dispatcher']->dispatch(ExceptionEvent::ERROR, new ExceptionEvent($e));
            } catch (\Exception $e) {
                $app['logger.system']->critical('[ClientLogin][Controller] Event dispatcher had an error', ['event' => 'exception', 'exception' => $e]);
            }
        }

        $app['clientlogin.feedback']->set('debug', $e->getMessage());
        $response->setContent($app['clientlogin.ui']->displayExceptionPage($e));

        return $response;
    }

    /**
     * For now have a fit if the responses are invalid.
     *
     * @param Response $response
     *
     * @throws \Exception
     *
     * @internal
     */
    private function isResponseValid(Response $response)
    {
        if ($response instanceof SuccessRedirectResponse) {
            return;
        }

        if ($response instanceof FailureResponse) {
            return;
        }

        throw new \Exception('ClientLogin handler returned a response of type: ' . get_class($response) . ' and must be either SuccessRedirectResponse or FailureResponse');
    }

    /**
     * Save the redirect URL to the session.
     *
     * @param \Silex\Application $app
     * @param Request            $request
     *
     * @return string
     */
    private function setFinalRedirectUrl(Application $app, Request $request)
    {
        if ($returnpage = $request->get('redirect')) {
            $returnpage = str_replace($app['resources']->getUrl('hosturl'), '', $returnpage);
        } else {
            $returnpage = $app['resources']->getUrl('hosturl');
        }

        $app['session']->set(self::FINAL_REDIRECT_KEY, $returnpage);

        return $returnpage;
    }

    /**
     * Get the saved redirect URL from the session.
     *
     * @param \Silex\Application $app
     *
     * @return string
     */
    private function getRedirectUrl($app)
    {
        if ($returnpage = $app['session']->get(self::FINAL_REDIRECT_KEY)) {
            return $returnpage;
        }

        return $app['resources']->getUrl('hosturl');
    }

    /**
     * Clear the redirect URL.
     *
     * @param \Silex\Application $app
     */
    private function clearRedirectUrl($app)
    {
        $app['session']->remove(self::FINAL_REDIRECT_KEY);
    }
}
