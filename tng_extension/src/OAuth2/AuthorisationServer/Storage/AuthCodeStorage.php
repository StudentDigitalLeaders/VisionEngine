<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\AuthorisationServer\Storage;

use Doctrine\DBAL\Driver\Connection;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AuthCodeInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Methods for retrieving, creating and deleting authorization codes.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AuthCodeStorage implements AuthCodeInterface
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
     * {@inheritdoc}
     */
    public function associateScope(AuthCodeEntity $token, ScopeEntity $scope)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function create($token, $expireTime, $sessionId, $redirectUri)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AuthCodeEntity $token)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get($code)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes(AuthCodeEntity $token)
    {
    }
}
