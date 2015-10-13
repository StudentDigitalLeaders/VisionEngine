<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\AuthorisationServer;

use Doctrine\DBAL\Driver\Connection;
use League\Event\EventInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\Exception\OAuthException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Server\Event\ClientAuthenticationFailedEvent;
use League\OAuth2\Server\Event\UserAuthenticationFailedEvent;
use League\OAuth2\Server\Event\SessionOwnerEvent;
use League\OAuth2\Server\Grant\RefreshTokenGrant;

/**
 * Local OAuth server manager.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Server
{
    /** @var \Doctrine\DBAL\Driver\Connection */
    protected $db;
    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface */
    protected $session;
    /** @var \League\OAuth2\Server\ResourceServer */
    protected $resourceServer;
    /** @var \League\OAuth2\Server\AuthorizationServer */
    protected $authorisationServer;

    /**
     * Constructor.
     *
     * @param Connection       $db
     * @param SessionInterface $session
     */
    public function __construct(Connection $db, SessionInterface $session)
    {
        $this->db = $db;
        $this->session = $session;

        $this->setResourceServer();
    }

    /**
     * Authorization code grant.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function grantAuthorisationCode(Request $request)
    {
        $this->authorisationServer->setRequest($request);

        try {
            // Ensure the parameters in the query string are correct
            $authParams = $this->authorisationServer
                ->getGrantType('authorization_code')
                ->checkAuthorizeParams();

            // Everything is okay, save $authParams to the a session and
            // redirect the user to sign-in
            $this->storeAuthParams($authParams);

            return new Response('', 302, [
                'Location'  =>  '/signin'
            ]);
        } catch (OAuthException $e) {
            if ($e->shouldRedirect()) {
                return new Response('', 302, [
                    'Location'  =>  $e->getRedirectUri()
                ]);
            }

            return $this->getExceptionResponse($e);
        }
    }

    /**
     * Client credentials grant.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function grantClientCredentials(Request $request)
    {
        $this->authorisationServer->setRequest($request);

        try {
            $response = $this->authorisationServer->issueAccessToken();

            return new Response(
                json_encode($response),
                200,
                [
                    'Content-type'  =>  'application/json',
                    'Cache-Control' =>  'no-store',
                    'Pragma'        =>  'no-store'
                ]
            );
        } catch (OAuthException $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * Resource owner password credentials grant.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function grantPasswordCredentials(Request $request)
    {
        $this->authorisationServer->setRequest($request);

        try {
            $response = $this->authorisationServer->issueAccessToken();

            return new Response(
                json_encode($response),
                200,
                [
                    'Content-type'  =>  'application/json',
                    'Cache-Control' =>  'no-store',
                    'Pragma'        =>  'no-store'
                ]
            );
        } catch (OAuthException $e) {
            return $this->getExceptionResponse($e);
        }
    }

    /**
     * Get a response object for an OAuthException.
     *
     * @param OAuthException $e
     *
     * @return Response
     */
    protected function getExceptionResponse(OAuthException $e)
    {
        return new Response(
            json_encode([
                'error'     =>  $e->errorType,
                'message'   =>  $e->getMessage()
            ]),
            $e->httpStatusCode,
            $e->getHttpHeaders()
        );
    }

    /**
     * Set up an OAuth2 Authorization Server.
     */
    protected function setAuthorizationServer()
    {
        $this->authorisationServer = new AuthorizationServer();

        // Storage
        $this->authorisationServer->setSessionStorage(new Storage\SessionStorage());
        $this->authorisationServer->setAccessTokenStorage(new Storage\AccessTokenStorage());
        $this->authorisationServer->setClientStorage(new Storage\ClientStorage());
        $this->authorisationServer->setScopeStorage(new Storage\ScopeStorage());
        $this->authorisationServer->setRefreshTokenStorage(new Storage\RefreshTokenStorage());

        // Grants
        $this->authorisationServer->addGrantType(new RefreshTokenGrant());

        // Events
        $this->authorisationServer->addEventListener('error.auth.client', [$this, 'eventErrorAuthClient']);
        $this->authorisationServer->addEventListener('error.auth.user', [$this, 'eventErrorAuthUser']);
        $this->authorisationServer->addEventListener('session.owner', [$this, 'eventSessionOwner']);
    }

    /**
     * Set up an OAuth2 Resource Server.
     */
    protected function setResourceServer()
    {
        $sessionStorage = new Storage\SessionStorage();
        $accessTokenStorage = new Storage\AccessTokenStorage();
        $clientStorage = new Storage\ClientStorage();
        $scopeStorage = new Storage\ScopeStorage();

        $this->resourceServer = new ResourceServer(
            $sessionStorage,
            $accessTokenStorage,
            $clientStorage,
            $scopeStorage
        );
    }

    /**
     * Emitted when a client fails to authenticate.
     *
     * Listen to this event in order to ban clients that fail to authenticate
     * after 'n' number of attempts
     *
     * @param \League\OAuth2\Server\Event\ClientAuthenticationFailedEvent
     */
    public function eventErrorAuthClient(ClientAuthenticationFailedEvent $event)
    {
        $request = $event->getRequest();
    }

    /**
     * Emitted when a user fails to authenticate.
     *
     * Listen to this event in order to reset passwords or ban users that fail
     * to authenticate after 'n' number of attempts.
     *
     * @param \League\OAuth2\Server\Event\UserAuthenticationFailedEvent
     */
    public function eventErrorAuthUser(UserAuthenticationFailedEvent $event)
    {
        $request = $event->getRequest();
    }

    /**
     * Emitted when a session has been allocated an owner (for example a user or
     * a client).
     *
     * You might want to use this event to dynamically associate scopes to the
     * session depending on the users role or ACL permissions.
     *
     * @param \League\OAuth2\Server\Event\SessionOwnerEvent
     */
    public function eventSessionOwner(SessionOwnerEvent $event)
    {
        $session = $event->getSession();
    }
}
