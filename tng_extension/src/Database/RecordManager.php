<?php

namespace Bolt\Extension\Bolt\ClientLogin\Database;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Authenticated user record maintenance
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class RecordManager extends RecordManagerBase
{
    /**
     * Insert an account.
     *
     * @param string|null $guid
     * @param string|null $resourceOwnerId
     * @param string|null $passwordHash
     * @param string|null $emailAddress
     * @param boolean     $enabled
     *
     * @return \Doctrine\DBAL\Driver\Statement|integer|null
     */
    public function insertAccount($guid, $resourceOwnerId, $passwordHash, $emailAddress, $enabled = false)
    {
        $query = $this->getAccountQueriesWrite()->queryInsert($guid, $resourceOwnerId, $passwordHash, $emailAddress, $enabled);

        return $this->executeQuery($query);
    }

    /**
     * Set an account password.
     *
     * @param string $resourceOwnerId
     * @param string $passwordHash
     *
     * @return \Doctrine\DBAL\Driver\Statement|integer|null
     */
    public function setAccountPassword($resourceOwnerId, $passwordHash)
    {
        $query = $this->getAccountQueriesWrite()->querySetPasswordByResourceOwnerId($resourceOwnerId, $passwordHash);

        return $this->executeQuery($query);
    }

    /**
     * Set an account enabled status.
     *
     * @param string  $guid
     * @param boolean $enabled
     *
     * @return \Doctrine\DBAL\Driver\Statement|integer|null
     */
    public function setAccountEnabledStatus($guid, $enabled)
    {
        $query = $this->getAccountQueriesWrite()->querySetEnableByResourceOwnerId($guid, $enabled);

        return $this->executeQuery($query);
    }

    /**
     * Get an account by GUID.
     *
     * @param string $guid
     *
     * @return \Doctrine\DBAL\Driver\Statement|integer|null
     */
    public function getAccountByGuid($guid)
    {
        $query = $this->getAccountQueriesRead()->queryFetchByGuid($guid);

        return $this->fetchArray($query);
    }

    /**
     * Get an account by resource owner ID.
     *
     * @param string $resourceOwnerId
     *
     * @return \Doctrine\DBAL\Driver\Statement|integer|null
     */
    public function getAccountByResourceOwnerId($resourceOwnerId)
    {
        $query = $this->getAccountQueriesRead()->queryFetchByResourceOwnerId($resourceOwnerId);

        return $this->fetchArray($query);
    }

    /**
     * Get the profile records for a GUID.
     *
     * @param string $guid
     *
     * @return array[]|false
     */
    public function getProfilesByGuid($guid)
    {
        $query = $this->getProviderQueriesRead()->queryFetchByGuid($guid);

        return $this->fetchArray($query);
    }

    /**
     * Get the profile records for a GUID.
     *
     * @param string $providerName
     * @param string $resourceOwnerId
     *
     * @return array|false
     */
    public function getProfileByResourceOwnerId($providerName, $resourceOwnerId)
    {
        $query = $this->getProviderQueriesRead()->queryFetchByResourceOwnerId($providerName, $resourceOwnerId);

        return $this->fetchArray($query);
    }

    /**
     * Get session records by GUID.
     *
     * @param string $guid
     *
     * @return array|false
     */
    public function getProviderSessions($guid)
    {
        $query = $this->getSessionQueriesRead()->queryFetchByGuid($guid);

        return $this->fetchArray($query);
    }

    /**
     * Get session record by access token ID.
     *
     * @param string $accessTokenId
     *
     * @return array|false
     */
    public function getProviderByAccessTokenId($accessTokenId)
    {
        $query = $this->getSessionQueriesRead()->queryFetchByAccessToken($accessTokenId);

        return $this->fetchArray($query);
    }

    /**
     * Get a profile record by provider and resource owner ID.
     *
     * @param string $provider
     * @param string $resourceOwnerId
     *
     * @return array|false
     */
    public function getProviderByResourceOwnerId($provider, $resourceOwnerId)
    {
        $query = $this->getProviderQueriesRead()->queryFetchByResourceOwnerId($provider, $resourceOwnerId);

        return $this->fetchArray($query);
    }

    /**
     * Insert a user profile.
     *
     * @param string                 $guid
     * @param string                 $provider
     * @param AccessToken            $accessToken
     * @param ResourceOwnerInterface $resourceOwner
     *
     * @return \Doctrine\DBAL\Driver\Statement|integer|null
     */
    public function insertProvider($guid, $provider, AccessToken $accessToken, ResourceOwnerInterface $resourceOwner)
    {
        $resourceOwnerId = $resourceOwner->getId();
        $query = $this->getProviderQueriesWrite()->queryInsert($guid, $provider, $resourceOwnerId, $accessToken, $resourceOwner);

        return $this->executeQuery($query);
    }

    /**
     * Update a user profile.
     *
     * @param string                 $guid
     * @param string                 $provider
     * @param ResourceOwnerInterface $resourceOwner
     *
     * @return \Doctrine\DBAL\Driver\Statement|integer|null
     */
    public function updateProvider($guid, $provider, ResourceOwnerInterface $resourceOwner)
    {
        $resourceOwnerId = $resourceOwner->getId();
        $query = $this->getProviderQueriesWrite()->queryUpdate($guid, $provider, $resourceOwnerId, $resourceOwner);

        return $this->executeQuery($query);
    }

    /**
     * Insert or update a session record for a user's access token.
     *
     * @param string      $guid
     * @param AccessToken $accessToken
     *
     * @return \Doctrine\DBAL\Driver\Statement|integer|null
     */
    public function writeSession($guid, AccessToken $accessToken)
    {
        $session = $this->getProviderSessions($guid);

        if ($session) {
            $query = $this->getSessionQueriesWrite()->queryUpdate($guid, $accessToken);
        } else {
            $query = $this->getSessionQueriesWrite()->queryInsert($guid, $accessToken);
        }

        return $this->executeQuery($query);
    }
}
