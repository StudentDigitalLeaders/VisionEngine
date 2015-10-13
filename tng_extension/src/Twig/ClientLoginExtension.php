<?php

namespace Bolt\Extension\Bolt\ClientLogin\Twig;

use Bolt\Application;
use Bolt\Extension\Bolt\ClientLogin\Extension;
use Bolt\Extension\Bolt\ClientLogin\Twig\Helper\UserInterface;

/**
 * Twig functions
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ClientLoginExtension extends \Twig_Extension
{
    /** @var Application */
    private $app;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Extension::NAME . '_Twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        // @codingStandardsIgnoreStart
        return [
            new \Twig_SimpleFunction('hasauth',       [$this, 'getHasAuth'],       ['is_safe' => ['html'], 'is_safe_callback' => true]),
            new \Twig_SimpleFunction('profile',       [$this, 'getWhoAmI'],        ['is_safe' => ['html'], 'is_safe_callback' => true]),
            new \Twig_SimpleFunction('displayauth',   [$this, 'getDisplayAuth'],   ['is_safe' => ['html'], 'is_safe_callback' => true]),
            new \Twig_SimpleFunction('displaylogin',  [$this, 'getDisplayLogin'],  ['is_safe' => ['html'], 'is_safe_callback' => true]),
            new \Twig_SimpleFunction('displaylogout', [$this, 'getDisplayLogout'], ['is_safe' => ['html'], 'is_safe_callback' => true]),
        ];
        // @codingStandardsIgnoreEnd
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return ['clientlogin' => ['feedback' => $this->app['clientlogin.feedback']]];
    }

    /**
     * Check login status.
     *
     * @return boolean
     */
    public function getHasAuth()
    {
        if ($this->app['clientlogin.session']->isLoggedIn()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get profile if user is logged in.
     *
     * If the userr is not logged in just return empty array values.
     *
     * @return array
     */
    public function getWhoAmI()
    {
        $visitor = array(
            'id'       => null,
            'username' => null,
            'email'    => null,
            'provider' => null
        );
        $profile = $this->app['clientlogin.session']->isLoggedIn();
        if ($profile) {
            //dump($profile);
            $visitor['id'] = $profile->id;
            $visitor['provider'] = $profile->provider;
            // do some testing for sensible defaults
            if ($profile->name) {
                $visitor['username'] = $profile->name;
            } elseif ($profile->firstName && $profile->lastName) {
                $visitor['username'] = $profile->firstName . ' ' . $profile->lastName;
            } elseif ($profile->lastName) {
                $visitor['username'] = $profile->lastName;
            } elseif ($profile->nickname) {
                $visitor['username'] = $profile->nickname;
            } else {
                $visitor['username'] = "user ". $profile->id;
            }
            if (!empty($profile->email)) {
                $visitor['email'] = $profile->email;
            }
            return $visitor;
        } else {
            return $visitor;
        }
    }

    /**
     * Display login/logout depending on status.
     *
     * @param boolean $redirect
     *
     * @return \Twig_Markup
     */
    public function getDisplayAuth($redirect = false)
    {
        return $this->getUserInterface()->displayAuth($redirect);
    }

    /**
     * Display login buttons.
     *
     * @param boolean $redirect
     *
     * @return \Twig_Markup
     */
    public function getDisplayLogin($redirect = false)
    {
        return $this->getUserInterface()->displayLogin($redirect);
    }

    /**
     * Display logout button.
     *
     * @param boolean $redirect
     *
     * @return \Twig_Markup
     */
    public function getDisplayLogout($redirect = false)
    {
        return $this->getUserInterface()->displayLogout($redirect);
    }

    /**
     * Return a UserInterface object and ensure our Twig global extists.
     *
     * @return UserInterface
     */
    protected function getUserInterface()
    {
        return $this->app['clientlogin.ui'];
    }
}
