<?php

namespace Bolt\Extension\Bolt\ClientLogin\Authorisation;

use Bolt\Extension\Bolt\ClientLogin\Exception;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RandomLib\Generator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Token management class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class TokenManager
{
    /** Session key name of the access token ID */
    const TOKEN_ACCESS = 'bolt.clientlogin.token.access';
    /** Session key name of the state value used on authentication request to upstream */
    const TOKEN_STATE = 'bolt.clientlogin.token.state';
    const TOKEN_CODE = 'bolt.clientlogin.token.code';
    const TOKEN_COOKIE_NAME = 'clientlogin_access_token';
    const TOKEN_SESSION_NAME = 'clientlogin_session_token';

    /** \Symfony\Component\HttpFoundation\Session\SessionInterface */
    protected $session;
    /** @var \RandomLib\Generator */
    protected $random;
    /** @var \Psr\Log\LoggerInterface */
    protected $log;

    /**
     * Constructor.
     *
     * @param SessionInterface $session
     * @param Generator        $random
     * @param LoggerInterface  $log
     */
    public function __construct(SessionInterface $session, Generator $random, LoggerInterface $log)
    {
        $this->session = $session;
        $this->random = $random;
        $this->log = $log;
    }

    /**
     * Get a session token.
     *
     * @param string $tokenName
     *
     * @return SessionToken|string|null
     */
    public function getToken($tokenName)
    {
        if ($token = $this->session->get($tokenName)) {
            $this->logMessage(LogLevel::DEBUG, "Retrieved '$tokenName' token. Value: '" . (string) $token . "'.");
        } else {
            $this->logMessage(LogLevel::DEBUG, "Token '$tokenName' does not exist.");
        }

        return $token;
    }

    /**
     * Remove a session token.
     *
     * @param string $tokenName
     */
    public function removeToken($tokenName)
    {
        $this->logMessage(LogLevel::DEBUG, "Clearing '$tokenName' token.");

        $this->session->remove($tokenName);
    }

    /**
     * Save an SessionToken to the session.
     *
     * @param string      $guid
     * @param AccessToken $accessToken
     *
     * @throws \RuntimeException
     */
    public function setAuthToken($guid, AccessToken $accessToken)
    {
        $sessionToken = new SessionToken($guid, $accessToken);
        $this->session->set(self::TOKEN_ACCESS, $sessionToken);
        $this->logMessage(LogLevel::DEBUG, sprintf("Setting '%s' token. Value: '%s'", self::TOKEN_ACCESS, (string) $sessionToken));

        // Retrive the saved token to make sure that the Session is working properly
        $accessToken = $this->getToken(self::TOKEN_ACCESS);

        if (!$accessToken instanceof SessionToken) {
            throw new Exception\SystemSetupException('[ClientLogin] Unable to create a Symfony session token!');
        }
    }

    /**
     * Save a authorisation code string to the session.
     *
     * @throws Exception\SystemSetupException
     *
     * @return string
     */
    public function setCodeToken()
    {
        $codeValue = $this->random->generateString(32);
        $this->session->set(self::TOKEN_CODE, $codeValue);

        // Retrive the saved token to make sure that the Session is working properly
        $codeValue = $this->getToken(self::TOKEN_CODE);

        if (empty($codeValue)) {
            throw new Exception\SystemSetupException('Unable to create a Symfony session token!');
        }

        return $codeValue;
    }

    /**
     * Save a state token to the session.
     *
     * @param $string
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function setStateToken($state)
    {
        if (empty($state)) {
            $this->logMessage(LogLevel::DEBUG, 'Trying to set empty state token!');
            throw new Exception\SystemSetupException('Trying to set empty state token!');
        }

        $this->logMessage(LogLevel::DEBUG, sprintf("Setting '%s' token. Value: '%s'", self::TOKEN_STATE, $state));
        $this->session->set(self::TOKEN_STATE, $state);

        // Retrive the saved token to make sure that the Session is working properly
        $token = $this->getToken(self::TOKEN_STATE);

        if (empty($token)) {
            throw new Exception\SystemSetupException('Unable to create a Symfony session token!');
        }

        return $token;
    }

    /**
     * Check if a request's state token matches the session one.
     *
     * @param Request $request
     *
     * @throws InvalidAuthorisationRequestException
     *
     * @return boolean
     */
    public function checkStateToken(Request $request)
    {
        $state = $request->get('state');
        if ($state === null) {
            $this->logMessage(LogLevel::ERROR, 'Authorisation request was missing state token.');
            throw new Exception\InvalidAuthorisationRequestException('Invalid authorisation request!');
        }

        // Get the stored token
        $stateToken = $this->getToken(self::TOKEN_STATE);

        // Clear the stored token from the session
        $this->removeToken(self::TOKEN_STATE);

        if (empty($stateToken) || $stateToken !== $state) {
            $this->logMessage(LogLevel::ERROR, "Mismatch of state token '$state' against saved '$stateToken'");

            return false;
        }

        return true;
    }

    /**
     * Write a log message.
     *
     * @param string $message
     */
    protected function logMessage($level, $message)
    {
        $this->log->log($level, '[ClientLogin][Token]: ' . $message, ['event' => 'extensions']);
    }
}
