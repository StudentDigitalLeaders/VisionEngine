<?php

namespace Bolt\Extension\Bolt\ClientLogin\Authorisation;

use League\OAuth2\Client\Token\AccessToken;

/**
 * Authenitication token class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SessionToken implements \JsonSerializable
{
    /** @var string */
    protected $guid;
    /** @var AccessToken */
    protected $accessToken;

    /**
     * Constructor.
     *
     * @param string      $guid
     * @param AccessToken $accessToken
     *
     * @throws \RuntimeException
     */
    public function __construct($guid, AccessToken $accessToken)
    {
        if (strlen($guid) !== 36) {
            throw new \RuntimeException('Invalid GUID value passed to token contructor!');
        }

        $this->guid = $guid;
        $this->accessToken = $accessToken;
    }

    /**
     * Get the GUID.
     *
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * Get the access token.
     *
     * @return AccessToken
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Get the access token ID.
     *
     * @return string
     */
    public function getAccessTokenId()
    {
        return (string) $this->accessToken;
    }

    /**
     * Return our values as a string in the form of:
     *   GUID||token ID||resource owner ID
     *
     * @return string
     */
    public function __toString()
    {
        //
        return sprintf('%s||%s||%s',
            $this->guid,
            (string) $this->accessToken,
            $this->accessToken->getResourceOwnerId()
            );
    }

    public function jsonSerialize()
    {
        $returnArray = [
            'guid'        => $this->guid,
            'accessToken' => $this->accessToken,
        ];

        return json_encode($returnArray);
    }
}
