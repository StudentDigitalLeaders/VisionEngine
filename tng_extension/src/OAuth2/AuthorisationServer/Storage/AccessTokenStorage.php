<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\AuthorisationServer\Storage;

use Doctrine\DBAL\Driver\Connection;
use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AccessTokenInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Methods for retrieving, creating and deleting access tokens.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AccessTokenStorage implements AccessTokenInterface
{
    /** @var \Doctrine\DBAL\Driver\Connection */
    protected $db;
    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface */
    protected $session;

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
    }

    /**
     * {@inheridoc}
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
    {
    }

    /**
     * {@inheridoc}
     */
    public function create($token, $expireTime, $sessionId)
    {
    }

    /**
     * {@inheridoc}
     */
    public function delete(AccessTokenEntity $token)
    {
    }

    /**
     * {@inheridoc}
     */
    public function get($token)
    {
    }

    /**
     * {@inheridoc}
     */
    public function getScopes(AccessTokenEntity $token)
    {
    }
}
