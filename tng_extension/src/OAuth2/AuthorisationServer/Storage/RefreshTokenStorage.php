<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\AuthorisationServer\Storage;

use Doctrine\DBAL\Driver\Connection;
use League\OAuth2\Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Storage\RefreshTokenInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Methods for retrieving, creating and deleting refresh tokens.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class RefreshTokenStorage implements RefreshTokenInterface
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
    public function create($token, $expireTime, $accessToken)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RefreshTokenEntity $token)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function get($token)
    {
    }
}
