<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database\Query;

/**
 * Client profile table queries.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ProviderDelete extends QueryBase
{
    /**
     * Query to delete a profile based on provider and ID.
     *
     * @param string $provider
     * @param string $resourceOwnerId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryDelete($provider, $resourceOwnerId)
    {
        return $this->getQueryBuilder()
            ->delete($this->tableNameProvider, 'p')
            ->innerJoin('p', $this->tableNameTokens, 's', 'p.guid = s.guid')
            ->where('provider  = :provider')
            ->andWhere('resource_owner_id  = :resource_owner_id')
            ->setParameter(':provider', $provider)
            ->setParameter(':resource_owner_id', $resourceOwnerId)
        ;
    }
}
