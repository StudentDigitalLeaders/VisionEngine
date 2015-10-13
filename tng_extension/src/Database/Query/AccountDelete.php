<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database\Query;

/**
 * Client account table delete queries.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AccountDelete extends QueryBase
{
    /**
     * Query to delete an account based on GUID.
     *
     * @param string $guid
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryDelete($guid)
    {
        return $this->getQueryBuilder()
            ->delete($this->tableNameAccount, 'a')
            ->innerJoin('a', $this->tableNameProvider, 'p', 'a.guid = p.guid')
            ->innerJoin('a', $this->tableNameTokens, 's', 'a.guid = s.guid')
            ->where('guid  = :guid')
            ->setParameter(':guid', $guid)
        ;
    }
}
