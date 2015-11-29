<?php

namespace Kanboard\Auth;

use Kanboard\Core\Base;
use Kanboard\Core\Security\OAuthAuthenticationProviderInterface;
use Kanboard\User\GitlabUserProvider;

/**
 * Gitlab Authentication Provider
 *
 * @package  auth
 * @author   Frederic Guillot
 */
class GitlabAuth extends Base implements OAuthAuthenticationProviderInterface
{
    /**
     * User properties
     *
     * @access private
     * @var \Kanboard\User\GitlabUserProvider
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
        return 'Gitlab';
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
            $this->user = new GitlabUserProvider($profile);
            return true;
        }

        return false;
    }

    /**
     * Set Code
     *
     * @access public
     * @param  string  $code
     * @return GitlabAuth
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
     * @return null|\Kanboard\User\GitlabUserProvider
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
                GITLAB_CLIENT_ID,
                GITLAB_CLIENT_SECRET,
                $this->helper->url->to('oauth', 'gitlab', array(), '', true),
                GITLAB_OAUTH_AUTHORIZE_URL,
                GITLAB_OAUTH_TOKEN_URL,
                array()
            );
        }

        return $this->service;
    }

    /**
     * Get Gitlab profile
     *
     * @access private
     * @return array
     */
    private function getProfile()
    {
        $this->getService()->getAccessToken($this->code);

        return $this->httpClient->getJson(
            GITLAB_API_URL.'user',
            array($this->getService()->getAuthorizationHeader())
        );
    }
}
