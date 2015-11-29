<?php

namespace Kanboard\Auth;

use Kanboard\Core\Base;
use Kanboard\Core\Security\PasswordAuthenticationProviderInterface;
use Kanboard\Model\User;
use Kanboard\User\DatabaseUserProvider;

/**
 * Database Authentication Provider
 *
 * @package  auth
 * @author   Frederic Guillot
 */
class DatabaseAuth extends Base implements PasswordAuthenticationProviderInterface
{
    /**
     * User properties
     *
     * @access private
     * @var array
     */
    private $userInfo = array();

    /**
     * Username
     *
     * @access private
     * @var string
     */
    private $username = '';

    /**
     * Password
     *
     * @access private
     * @var string
     */
    private $password = '';

    /**
     * Get authentication provider name
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return 'Database';
    }

    /**
     * Authenticate the user
     *
     * @access public
     * @return boolean
     */
    public function authenticate()
    {
        $user = $this->db
            ->table(User::TABLE)
            ->columns('id', 'password')
            ->eq('username', $this->username)
            ->eq('disable_login_form', 0)
            ->eq('is_ldap_user', 0)
            ->findOne();

        if (! empty($user) && password_verify($this->password, $user['password'])) {
            $this->userInfo = $user;
            return true;
        }

        return false;
    }

    /**
     * Get user object
     *
     * @access public
     * @return UserProviderInterface
     */
    public function getUser()
    {
        if (empty($this->userInfo)) {
            return null;
        }

        return new DatabaseUserProvider($this->userInfo);
    }

    /**
     * Set username
     *
     * @access public
     * @param  string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Set password
     *
     * @access public
     * @param  string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }
}
