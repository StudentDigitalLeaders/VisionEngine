<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database\Query;

/**
 * Client account table read queries.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AccountRead extends QueryBase
{
    /**
     * Query to fetch aa account by GUID.
     *
     * @param string $guid
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryFetchByGuid($guid)
    {
        return $this->getQueryBuilder()
            ->select('*')
            ->from($this->tableNameAccount, 'a')
            ->leftJoin('a', $this->tableNameProvider, 'p', 'a.guid = p.guid')
            ->where('a.guid = :guid')
            ->setParameter(':guid', $guid)
        ;
    }

    /**
     * Query to fetch a account by resource owner.
     *
     * @param string $resourceOwnerId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryFetchByResourceOwnerId($resourceOwnerId)
    {
        return $this->getQueryBuilder()
            ->select('*')
            ->from($this->tableNameAccount, 'a')
            ->leftJoin('a', $this->tableNameProvider, 'p', 'a.guid = p.guid')
            ->where('a.resource_owner_id = :resource_owner_id')
            ->setParameters([
                ':resource_owner_id' => $resourceOwnerId,
            ])
        ;
    }
}
