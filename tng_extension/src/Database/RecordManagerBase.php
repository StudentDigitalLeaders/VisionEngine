<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database;

use Bolt\Extension\Bolt\ClientLogin\Config;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Psr\Log\LoggerInterface;

/**
 * Authenticated user record maintenance
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class RecordManagerBase
{
    /** @var \Doctrine\DBAL\Driver\Connection */
    protected $db;
    /** @var \Bolt\Extension\Bolt\ClientLogin\Config */
    protected $config;
    /** @var LoggerInterface */
    protected $logger;
    /** @var string */
    protected $tableName;

    /**
     * Constructor.
     *
     * @param Connection      $db
     * @param Config          $config
     * @param LoggerInterface $logger
     * @param string          $tableName
     */
    public function __construct(
        Connection $db,
        Config $config,
        LoggerInterface $logger,
        $tableName
    ) {
        $this->db = $db;
        $this->config = $config;
        $this->logger = $logger;
        $this->tableName = $tableName;
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Get the account read query builder.
     *
     * @return \Bolt\Extension\Bolt\ClientLogin\Database\Query\AccountRead
     */
    protected function getAccountQueriesRead()
    {
        return new Query\AccountRead($this->db, $this->tableName);
    }

    /**
     * Get the account remove query builder.
     *
     * @return \Bolt\Extension\Bolt\ClientLogin\Database\Query\AccountDelete
     */
    protected function getAccountQueriesDelete()
    {
        return new Query\AccountDelete($this->db, $this->tableName);
    }

    /**
     * Get the account write query builder.
     *
     * @return \Bolt\Extension\Bolt\ClientLogin\Database\Query\AccountWrite
     */
    protected function getAccountQueriesWrite()
    {
        return new Query\AccountWrite($this->db, $this->tableName);
    }

    /**
     * Get the profile read query builder.
     *
     * @return \Bolt\Extension\Bolt\ClientLogin\Database\Query\ProviderRead
     */
    protected function getProviderQueriesRead()
    {
        return new Query\ProviderRead($this->db, $this->tableName);
    }

    /**
     * Get the profile remove query builder.
     *
     * @return \Bolt\Extension\Bolt\ClientLogin\Database\Query\ProviderDelete
     */
    protected function getProviderQueriesDelete()
    {
        return new Query\ProviderDelete($this->db, $this->tableName);
    }

    /**
     * Get the profile write query builder.
     *
     * @return \Bolt\Extension\Bolt\ClientLogin\Database\Query\ProviderWrite
     */
    protected function getProviderQueriesWrite()
    {
        return new Query\ProviderWrite($this->db, $this->tableName);
    }

    /**
     * Get the session read query builder.
     *
     * @return \Bolt\Extension\Bolt\ClientLogin\Database\Query\SessionRead
     */
    protected function getSessionQueriesRead()
    {
        return new Query\SessionRead($this->db, $this->tableName);
    }

    /**
     * Get the session remove query builder.
     *
     * @return \Bolt\Extension\Bolt\ClientLogin\Database\Query\SessionDelete
     */
    protected function getSessionQueriesDelete()
    {
        return new Query\SessionDelete($this->db, $this->tableName);
    }

    /**
     * Get the session write query builder.
     *
     * @return \Bolt\Extension\Bolt\ClientLogin\Database\Query\SessionWrite
     */
    protected function getSessionQueriesWrite()
    {
        return new Query\SessionWrite($this->db, $this->tableName);
    }

    /**
     * Execute a query.
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder
     *
     * @return \Doctrine\DBAL\Driver\Statement|integer|null
     */
    protected function executeQuery(QueryBuilder $query)
    {
        $this->logger->debug('[ClientLogin][Database]: ' . (string) $query, ['event' => 'extensions']);

        try {
            return $query->execute();
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->logger->critical('[ClientLogin][Database]: Database exception.', ['event' => 'exception', 'exception' => $e]);
            throw $e;
        }
    }

    /**
     * Execute a query and fetch the result as an associative array.
     *
     * @param \Doctrine\DBAL\Query\QueryBuilder
     *
     * @return array|false|null
     */
    protected function fetchArray(QueryBuilder $query)
    {
        $this->logger->debug('[ClientLogin][Database]: ' . (string) $query, ['event' => 'extensions']);

        try {
            return $query
                ->execute()
                ->fetch(\PDO::FETCH_ASSOC);
        } catch (\Doctrine\DBAL\DBALException $e) {
            $this->logger->critical('[ClientLogin][Database]: Database exception.', ['event' => 'exception', 'exception' => $e]);
            throw $e;
        }
    }
}
