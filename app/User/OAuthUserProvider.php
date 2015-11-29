<?php

namespace Kanboard\User;

use Kanboard\Core\User\UserProviderInterface;
use Kanboard\Core\Security\Role;

/**
 * OAuth User Provider
 *
 * @package  user
 * @author   Frederic Guillot
 */
abstract class OAuthUserProvider implements UserProviderInterface
{
    /**
     * Get external id column name
     *
     * @access public
     * @return string
     */
    abstract public function getExternalIdColumn();

    /**
     * User properties
     *
     * @access private
     * @var array
     */
    private $user = array();

    /**
     * Constructor
     *
     * @access public
     * @param  array $user
     */
    public function __construct(array $user)
    {
        $this->user = $user;
    }

    /**
     * Return true to allow automatic user creation
     *
     * @access public
     * @return boolean
     */
    public function isUserCreationAllowed()
    {
        return false;
    }

    /**
     * Get internal id
     *
     * @access public
     * @return string
     */
    public function getInternalId()
    {
        return '';
    }

    /**
     * Get external id
     *
     * @access public
     * @return string
     */
    public function getExternalId()
    {
        return $this->user['id'];
    }

    /**
     * Get user role
     *
     * @access public
     * @return string
     */
    public function getRole()
    {
        return Role::APP_USER;
    }

    /**
     * Get username
     *
     * @access public
     * @return string
     */
    public function getUsername()
    {
        return '';
    }

    /**
     * Get full name
     *
     * @access public
     * @return string
     */
    public function getName()
    {
        return $this->user['name'];
    }

    /**
     * Get user email
     *
     * @access public
     * @return string
     */
    public function getEmail()
    {
        return $this->user['email'];
    }

    /**
     * Get groups
     *
     * @access public
     * @return array
     */
    public function getGroups()
    {
        return array();
    }

    /**
     * Get extra user attributes
     *
     * @access public
     * @return array
     */
    public function getExtraAttributes()
    {
        return array();
    }
}
