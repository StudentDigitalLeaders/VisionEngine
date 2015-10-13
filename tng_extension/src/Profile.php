<?php

namespace Bolt\Extension\Bolt\ClientLogin;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Client profile data class
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Profile implements \JsonSerializable
{
    protected $id;
    protected $provider;
    protected $uid;
    protected $enabled = true;
    protected $nickname;
    protected $name;
    protected $firstName;
    protected $lastName;
    protected $email;
    protected $location;
    protected $description;
    protected $imageUrl;
    protected $urls;
    protected $gender;
    protected $locale;
    protected $password;

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNickname()
    {
        return $this->nickname;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->urls;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return boolean
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param string $name
     *
     * @throws \OutOfRangeException
     *
     * @return string
     */
    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $name
            ));
        }

        return $this->{$name} ? : '';
    }

    /**
     * @param string $property
     * @param string $value
     *
     * @throws \OutOfRangeException
     *
     * @return \Bolt\Extension\Bolt\ClientLogin\Client
     */
    public function __set($property, $value)
    {
        if (!property_exists($this, $property)) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $property
            ));
        }

        $this->$property = $value;

        return $this;
    }

    /**
     * Valid output for json_encode()
     *
     * @return array
     */
    public function jsonSerialize()
    {
        $arr = [];
        foreach (array_keys(get_class_vars(__CLASS__)) as $property) {
            $arr[$property] = $this->$property;
        }

        return $arr;
    }

    /**
     * Create a class instance from our database records.
     *
     * Just… don't… ask… :-/
     *
     * @param array|boolean $user
     *
     * @return Profile
     */
    public static function createFromDbRecord($user)
    {
        if (empty($user)) {
            return false;
        }

        $classname = get_called_class();
        $class = new $classname();

        $data = json_decode($user['providerdata'], true);

        $class->id          = $user['id'];
        $class->provider    = $user['provider'];
        $class->password    = isset($data['password'])   ? $data['password']   : '';
        $class->uid         = isset($data['identifier']) ? $data['identifier'] : $data['uid'];
        $class->enabled     = $data['enabled'];
        $class->nickname    = $data['nickname'];
        $class->name        = $data['name'];
        $class->firstName   = $data['firstName'];
        $class->lastName    = $data['lastName'];
        $class->email       = $data['email'];
        $class->location    = $data['location'];
        $class->description = $data['description'];
        $class->imageUrl    = $data['imageUrl'];
        $class->urls        = $data['urls'];
        $class->gender      = $data['gender'];
        $class->locale      = $data['locale'];

        return $class;
    }

    /**
     * Add an OAuth2 client data
     *
     * @param string                 $provider
     * @param ResourceOwnerInterface $resourceOwner
     *
     * @return Profile
     */
    public static function createFromResourceOwner($provider, ResourceOwnerInterface $resourceOwner)
    {
        $classname = get_called_class();
        $class = new $classname();

        $class->provider  = $provider;
        $class->uid       = $resourceOwner->getId();
        $class->nickname  = $resourceOwner->getNickname();
        $class->name      = $resourceOwner->getName();
        $class->firstName = $resourceOwner->getFirstName();
        $class->lastName  = $resourceOwner->getLastName();
        $class->email     = $resourceOwner->getEmail();
        $class->imageUrl  = $resourceOwner->getImageurl();
        $class->urls      = $resourceOwner->getUrl();

        return $class;
    }

    /**
     * Create an instance from password data.
     *
     * @param string $userName
     * @param string $password
     *
     * @return Profile
     */
    public static function createPasswordAuth($username, $password)
    {
        $classname = get_called_class();
        $class = new $classname();

        $class->uid         = $username;
        $class->provider    = 'Password';
        $class->password    = $password;
        $class->nickname    = $username;
        $class->name        = $username;
        $class->firstName   = '';
        $class->lastName    = '';
        $class->email       = '';
        $class->location    = '';
        $class->description = '';
        $class->imageUrl    = '';
        $class->urls        = '';
        $class->gender      = '';
        $class->locale      = '';

        return $class;
    }
}
