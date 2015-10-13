<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database\Query;

use League\OAuth2\Client\Token\AccessToken;

/**
 * Client session write queries.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SessionWrite extends QueryBase
{
    /**
     * Query to insert a session record.
     *
     * @param string      $guid
     * @param AccessToken $accessToken
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryInsert($guid, AccessToken $accessToken)
    {
        return $this->getQueryBuilder()
            ->insert($this->tableNameTokens)
            ->values([
                'guid'              => ':guid',
                'access_token'      => ':access_token',
                'access_token_data' => ':access_token_data',
                'expires'           => ':expires',
            ])
            ->setParameters([
                'guid'              => $guid,
                'access_token'      => (string) $accessToken,
                'access_token_data' => json_encode($accessToken),
                'expires'           => $accessToken->getExpires(),
            ])
        ;
    }

    /**
     * Query to update a session record.
     *
     * @param string      $guid
     * @param AccessToken $accessToken
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryUpdate($guid, AccessToken $accessToken)
    {
        return $this->getQueryBuilder()
            ->update($this->tableNameTokens)
            ->set('access_token', ':access_token')
            ->set('access_token_data', ':access_token_data')
            ->set('expires', ':expires')
            ->where('guid  = :guid')
            ->andWhere('access_token  = :access_token')
            ->setParameters([
                'guid'              => $guid,
                'access_token'      => (string) $accessToken,
                'access_token_data' => json_encode($accessToken),
                'expires'           => $accessToken->getExpires(),
            ])
        ;
    }
}
