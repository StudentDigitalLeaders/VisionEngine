<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database\Query;

/**
 * Client session delete queries.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SessionDelete extends QueryBase
{
    /**
     * Query to delete all access token records (
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryDelete($accessTokenValue)
    {
        return $this->getQueryBuilder()
            ->delete($this->tableNameTokens)
            ->where('access_token  = :access_token')
            ->setParameter(':access_token', $accessTokenValue)
        ;
    }
}
