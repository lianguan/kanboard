<?php

namespace Kanboard\Auth;

use Kanboard\Core\Base;
use Kanboard\Core\Security\OAuthAuthenticationProviderInterface;
use Kanboard\User\GoogleUserProvider;

/**
 * Google Authentication Provider
 *
 * @package  auth
 * @author   Frederic Guillot
 */
class GoogleAuth extends Base implements OAuthAuthenticationProviderInterface
{
    /**
     * User properties
     *
     * @access private
     * @var \Kanboard\User\GoogleUserProvider
     */
    private $user = null;

    /**
     * OAuth2 instance
     *
     * @access private
     * @var \Kanboard\Core\Http\OAuth2
     */
    private $service;

    /**
     * OAuth2 code
     *
     * @access private
     * @var string
     */
    private $code = '';

    /**
     * Get authentication provider name
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return 'Google';
    }

    /**
     * Authenticate the user
     *
     * @access public
     * @return boolean
     */
    public function authenticate()
    {
        $profile = $this->getProfile();

        if (! empty($profile)) {
            $this->user = new GoogleUserProvider($profile);
            return true;
        }

        return false;
    }

    /**
     * Set Code
     *
     * @access public
     * @param  string  $code
     * @return GoogleAuth
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get user object
     *
     * @access public
     * @return null|GoogleUserProvider
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get configured OAuth2 service
     *
     * @access public
     * @return \Kanboard\Core\Http\OAuth2
     */
    public function getService()
    {
        if (empty($this->service)) {
            $this->service = $this->oauth->createService(
                GOOGLE_CLIENT_ID,
                GOOGLE_CLIENT_SECRET,
                $this->helper->url->to('oauth', 'google', array(), '', true),
                'https://accounts.google.com/o/oauth2/auth',
                'https://accounts.google.com/o/oauth2/token',
                array('https://www.googleapis.com/auth/userinfo.email', 'https://www.googleapis.com/auth/userinfo.profile')
            );
        }

        return $this->service;
    }

    /**
     * Get Google profile
     *
     * @access private
     * @return array
     */
    private function getProfile()
    {
        $this->getService()->getAccessToken($this->code);

        return $this->httpClient->getJson(
            'https://www.googleapis.com/oauth2/v1/userinfo',
            array($this->getService()->getAuthorizationHeader())
        );
    }
}
