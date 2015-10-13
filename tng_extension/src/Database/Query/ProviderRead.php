<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database\Query;

/**
 * Client profile table queries.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ProviderRead extends QueryBase
{
    /**
     * Query to fetch a profile by GUID.
     *
     * @param string $guid
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryFetchByGuid($guid)
    {
        return $this->getQueryBuilder()
            ->select('*')
            ->from($this->tableNameProvider)
            ->where('guid = :guid')
            ->setParameter(':guid', $guid)
        ;
    }

    /**
     * Query to fetch a profile by provider and ID.
     *
     * @param string $providerName
     * @param string $resourceOwnerId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryFetchByResourceOwnerId($providerName, $resourceOwnerId)
    {
        return $this->getQueryBuilder()
            ->select('*')
            ->from($this->tableNameProvider)
            ->where('provider = :provider')
            ->andWhere('resource_owner_id = :resource_owner_id')
            ->setParameter(':provider', $providerName)
            ->setParameter(':resource_owner_id', $resourceOwnerId)
        ;
    }
}
