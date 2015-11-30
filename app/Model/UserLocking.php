<?php

namespace Kanboard\Model;

/**
 * User Locking Model
 *
 * @package  model
 * @author   Frederic Guillot
 */
class UserLocking extends Base
{
    /**
     * Get the number of failed login for the user
     *
     * @access public
     * @param  string  $username
     * @return integer
     */
    public function getFailedLogin($username)
    {
        return (int) $this->db->table(User::TABLE)
            ->eq('username', $username)
            ->findOneColumn('nb_failed_login');
    }

    /**
     * Reset to 0 the counter of failed login
     *
     * @access public
     * @param  string  $username
     * @return boolean
     */
    public function resetFailedLogin($username)
    {
        return $this->db->table(User::TABLE)
            ->eq('username', $username)
            ->update(array(
                'nb_failed_login' => 0,
                'lock_expiration_date' => 0,
            ));
    }

    /**
     * Increment failed login counter
     *
     * @access public
     * @param  string  $username
     * @return boolean
     */
    public function incrementFailedLogin($username)
    {
        return $this->db->table(User::TABLE)
            ->eq('username', $username)
            ->increment('nb_failed_login', 1);
    }

    /**
     * Check if the account is locked
     *
     * @access public
     * @param  string  $username
     * @return boolean
     */
    public function isLocked($username)
    {
        return $this->db->table(User::TABLE)
            ->eq('username', $username)
            ->neq('lock_expiration_date', 0)
            ->gte('lock_expiration_date', time())
            ->exists();
    }

    /**
     * Lock the account for the specified duration
     *
     * @access public
     * @param  string   $username   Username
     * @param  integer  $duration   Duration in minutes
     * @return boolean
     */
    public function lock($username, $duration = 15)
    {
        return $this->db->table(User::TABLE)
            ->eq('username', $username)
            ->update(array(
                'lock_expiration_date' => time() + $duration * 60
            ));
    }
}
