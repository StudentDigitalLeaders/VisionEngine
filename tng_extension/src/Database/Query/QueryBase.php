<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database\Query;

use Doctrine\DBAL\Connection;

abstract class QueryBase
{
    /** @var \Doctrine\DBAL\Driver\Connection */
    protected $db;
    /** @var string */
    protected $tableNameBase;
    /** @var string */
    protected $tableNameAccount;
    /** @var string */
    protected $tableNameProvider;
    /** @var string */
    protected $tableNameTokens;

    public function __construct(Connection $db, $tableName)
    {
        $this->db = $db;
        $this->tableNameBase = $tableName;
        $this->tableNameAccount = $tableName . '_account';
        $this->tableNameProvider = $tableName . '_provider';
        $this->tableNameTokens = $tableName . '_tokens';
    }

    /**
     * Get the database connection
     *
     * @return \Doctrine\DBAL\Driver\Connection
     */
    protected function getConnection()
    {
        return $this->db;
    }

    /**
     * Get the database connection
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return $this->db->createQueryBuilder();
    }

    /**
     * Generate a v4 UUID.
     *
     * @return string
     */
    protected function getGuidV4()
    {
        $data = openssl_random_pseudo_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
