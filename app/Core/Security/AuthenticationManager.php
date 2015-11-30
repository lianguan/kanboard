<?php

namespace Kanboard\Core\Security;

use LogicException;
use Kanboard\Core\Base;
use Kanboard\Core\User\UserProviderInterface;
use Kanboard\Event\AuthFailureEvent;
use Kanboard\Event\AuthSuccessEvent;

/**
 * Authentication Manager
 *
 * @package  security
 * @author   Frederic Guillot
 */
class AuthenticationManager extends Base
{
    /**
     * Event names
     *
     * @var string
     */
    const EVENT_SUCCESS = 'auth.success';
    const EVENT_FAILURE = 'auth.failure';

    /**
     * List of authentication providers
     *
     * @access private
     * @var array
     */
    private $providers = array();

    /**
     * Register a new authentication provider
     *
     * @access public
     * @param  AuthenticationProviderInterface $provider
     * @return AuthenticationManager
     */
    public function register(AuthenticationProviderInterface $provider)
    {
        $this->providers[$provider->getName()] = $provider;
        return $this;
    }

    /**
     * Register a new authentication provider
     *
     * @access public
     * @param  string $name
     * @return AuthenticationProviderInterface|OAuthAuthenticationProviderInterface|PasswordAuthenticationProviderInterface|PreAuthenticationProviderInterface
     */
    public function getProvider($name)
    {
        if (! isset($this->providers[$name])) {
            throw new LogicException('Authentication provider not found: '.$name);
        }

        return $this->providers[$name];
    }

    /**
     * Execute pre-authentication providers
     *
     * @access public
     * @return boolean
     */
    public function preAuthentication()
    {
        foreach ($this->filterProviders('PreAuthenticationProviderInterface') as $provider) {
            if ($provider->authenticate() && $this->userProfile->initialize($provider->getUser())) {
                $this->dispatcher->dispatch(self::EVENT_SUCCESS, new AuthSuccessEvent($provider->getName()));
                return true;
            }
        }

        return false;
    }

    /**
     * Execute username/password authentication providers
     *
     * @access public
     * @param  string  $username
     * @param  string  $password
     * @return boolean
     */
    public function passwordAuthentication($username, $password)
    {
        foreach ($this->filterProviders('PasswordAuthenticationProviderInterface') as $provider) {
            $provider->setUsername($username);
            $provider->setPassword($password);

            if ($provider->authenticate() && $this->userProfile->initialize($provider->getUser())) {
                $this->dispatcher->dispatch(self::EVENT_SUCCESS, new AuthSuccessEvent($provider->getName()));
                return true;
            }
        }

        $this->dispatcher->dispatch(self::EVENT_FAILURE, new AuthFailureEvent);

        return false;
    }

    /**
     * Perform OAuth2 authentication
     *
     * @access public
     * @param  string  $name
     * @return boolean
     */
    public function oauthAuthentication($name)
    {
        $provider = $this->getProvider($name);

        if ($provider->authenticate() && $this->userProfile->initialize($provider->getUser())) {
            $this->dispatcher->dispatch(self::EVENT_SUCCESS, new AuthSuccessEvent($provider->getName()));
            return true;
        }

        $this->dispatcher->dispatch(self::EVENT_FAILURE, new AuthFailureEvent);

        return false;
    }

    /**
     * Get the last Post-Authentication provider
     *
     * @access public
     * @return PostAuthenticationProviderInterface
     */
    public function getPostAuthenticationProvider()
    {
        $providers = $this->filterProviders('PostAuthenticationProviderInterface');

        if (empty($providers)) {
            throw new LogicException('You must have at least one Post-Authentication Provider configured');
        }

        return array_pop($providers);
    }

    /**
     * Filter registered providers by interface type
     *
     * @access private
     * @param  string $interface
     * @return array
     */
    private function filterProviders($interface)
    {
        $interface = '\Kanboard\Core\Security\\'.$interface;

        return array_filter($this->providers, function(AuthenticationProviderInterface $provider) use ($interface) {
            return is_a($provider, $interface);
        });
    }
}
