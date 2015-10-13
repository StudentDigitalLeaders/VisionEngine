<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database\Query;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Client profile table queries.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ProviderWrite extends QueryBase
{
    /**
     * Query to insert a profile record.
     *
     * @param string                 $guid
     * @param string                 $provider
     * @param string                 $resourceOwnerId
     * @param AccessToken            $accessToken
     * @param ResourceOwnerInterface $resourceOwner
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryInsert($guid, $provider, $resourceOwnerId, AccessToken $accessToken, ResourceOwnerInterface $resourceOwner)
    {
        if ($guid === null) {
            $guid = $this->getGuidV4();
        }

        return $this->getQueryBuilder()
            ->insert($this->tableNameProvider)
            ->values([
                'guid'              => ':guid',
                'provider'          => ':provider',
                'resource_owner_id' => ':resource_owner_id',
                'refresh_token'     => ':refresh_token',
                'lastupdate'        => ':lastupdate',
                'resource_owner'    => ':resource_owner',
            ])
            ->setParameters([
                'guid'              => $guid,
                'provider'          => $provider,
                'resource_owner_id' => $resourceOwnerId,
                'refresh_token'     => $accessToken->getRefreshToken(),
                'lastupdate'        => date('Y-m-d H:i:s', time()),
                'resource_owner'    => json_encode($resourceOwner->toArray()),
            ])
        ;
    }

    /**
     * Query to update a user profile.
     *
     * @param string                 $guid
     * @param string                 $provider
     * @param string                 $resourceOwnerId
     * @param ResourceOwnerInterface $resourceOwner
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryUpdate($guid, $provider, $resourceOwnerId, ResourceOwnerInterface $resourceOwner)
    {
        $query = $this->getQueryBuilder()
            ->update($this->tableNameProvider)
            ->set('lastupdate',     ':lastupdate')
            ->set('resource_owner', ':resource_owner')
            ->where('provider  = :provider')
            ->andWhere('resource_owner_id  = :resource_owner_id')
            ->setParameters([
                'provider'          => $provider,
                'resource_owner_id' => $resourceOwnerId,
                'lastupdate'        => date('Y-m-d H:i:s', time()),
                'resource_owner'    => json_encode($resourceOwner->toArray()),
            ])
        ;

        if ($guid !== null) {
            $query->andWhere('resource_owner_id  = :resource_owner_id')
                ->setParameter('guid', ':guid')
            ;
        }

        return $query;
    }
}
