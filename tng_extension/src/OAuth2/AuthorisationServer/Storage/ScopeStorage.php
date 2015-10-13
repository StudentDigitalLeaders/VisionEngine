<?php

namespace Bolt\Extension\Bolt\ClientLogin\OAuth2\AuthorisationServer\Storage;

use Doctrine\DBAL\Driver\Connection;
use League\OAuth2\Server\Storage\ScopeInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Single method to get a scope.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ScopeStorage implements ScopeInterface
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
    public function get($scope, $grantType = null, $clientId = null)
    {
    }
}
