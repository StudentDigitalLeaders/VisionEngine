<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database\Query;

/**
 * Client account table write queries.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class AccountWrite extends QueryBase
{
    /**
     * Query to insert an account record.
     *
     * @param string  $guid
     * @param string  $resourceOwnerId
     * @param string  $passwordHash
     * @param string  $emailAddress
     * @param boolean $enabled
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryInsert($guid, $resourceOwnerId, $passwordHash, $emailAddress, $enabled)
    {
        if ($guid === null) {
            $guid = $this->getGuidV4();
        }

        return $this->getQueryBuilder()
            ->insert($this->tableNameAccount)
            ->values([
                'guid'              => ':guid',
                'resource_owner_id' => ':resource_owner_id',
                'password'          => ':password',
                'email'             => ':email',
                'enabled'           => ':enabled',
            ])
            ->setParameters([
                'guid'              => $guid,
                'resource_owner_id' => $resourceOwnerId,
                'password'          => $passwordHash,
                'email'             => $emailAddress,
                'enabled'           => $enabled,
            ])
        ;
    }

    /**
     * Query to set and account password.
     *
     * @param string  $guid
     * @param boolean $passwordHash
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function querySetPasswordByGuid($guid, $passwordHash)
    {
        return $this->getQueryBuilder()
            ->update($this->tableNameAccount)
            ->set('password', ':password')
            ->where('guid = :guid')
            ->setParameters([
                'guid'     => $guid,
                'password' => $passwordHash,
            ])
        ;
    }

    /**
     * Query to set and account password.
     *
     * @param string $resourceOwnerId
     * @param string $passwordHash
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function querySetPasswordByResourceOwnerId($resourceOwnerId, $passwordHash)
    {
        return $this->getQueryBuilder()
            ->update($this->tableNameAccount)
            ->set('password', ':password')
            ->where('resource_owner_id = :resource_owner_id')
            ->setParameters([
                'resource_owner_id' => $resourceOwnerId,
                'password'          => $passwordHash,
            ])
        ;
    }

    /**
     * Query to toggle the "enabled" value for an account record.
     *
     * @param string  $guid
     * @param boolean $enable
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function querySetEnableByGuid($guid, $enable)
    {
        return $this->getQueryBuilder()
            ->update($this->tableNameAccount)
            ->set('enabled', ':enabled')
            ->where('guid  = :gui')
            ->setParameters([
                'guid'    => $guid,
                'enabled' => $enable,
            ])
        ;
    }

    /**
     * Query to toggle the "enabled" value for an account record.
     *
     * @param string  $resourceOwnerId
     * @param boolean $enable
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function querySetEnableByResourceOwnerId($resourceOwnerId, $enable)
    {
        return $this->getQueryBuilder()
            ->update($this->tableNameAccount)
            ->set('enabled', ':enabled')
            ->where('resource_owner_id  = :resource_owner_id')
            ->setParameters([
                'resource_owner_id' => $resourceOwnerId,
                'enabled'           => $enable,
            ])
        ;
    }
}
