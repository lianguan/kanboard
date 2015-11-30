<?php

namespace Kanboard\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Kanboard\Core\Base;
use Kanboard\Core\Security\AuthenticationManager;
use Kanboard\Core\Session\SessionManager;
use Kanboard\Event\AuthSuccessEvent;
use Kanboard\Event\AuthFailureEvent;

/**
 * Authentication Subscriber
 *
 * @package subscriber
 * @author  Frederic Guillot
 */
class AuthSubscriber extends Base implements EventSubscriberInterface
{
    /**
     * Get event listeners
     *
     * @static
     * @access public
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            AuthenticationManager::EVENT_SUCCESS => 'afterLogin',
            AuthenticationManager::EVENT_FAILURE => 'onLoginFailure',
            SessionManager::EVENT_DESTROY => 'afterLogout',
        );
    }

    /**
     * After Login callback
     *
     * @access public
     * @param  AuthSuccessEvent $event
     */
    public function afterLogin(AuthSuccessEvent $event)
    {
        $userAgent = $this->request->getUserAgent();
        $ipAddress = $this->request->getIpAddress();

        $this->userLocking->resetFailedLogin($this->userSession->getUsername());

        $this->lastLogin->create(
            $event->getAuthType(),
            $this->userSession->getId(),
            $ipAddress,
            $userAgent
        );

        $this->sessionStorage->hasSubtaskInProgress = $this->subtask->hasSubtaskInProgress($this->userSession->getId());

        if (isset($this->sessionStorage->hasRememberMe) && $this->sessionStorage->hasRememberMe) {
            $session = $this->rememberMeSession->create($this->userSession->getId(), $ipAddress, $userAgent);
            $this->rememberMeCookie->write($session['token'], $session['sequence'], $session['expiration']);
        }
    }

    /**
     * Destroy RememberMe session on logout
     *
     * @access public
     */
    public function afterLogout()
    {
        $credentials = $this->rememberMeCookie->read();

        if ($credentials !== false) {
            $session = $this->rememberMeSession->find($credentials['token'], $credentials['sequence']);

            if (! empty($session)) {
                $this->rememberMeSession->remove($session['id']);
            }

            $this->rememberMeCookie->remove();
        }
    }

    /**
     * Increment failed login counter
     *
     * @access public
     */
    public function onLoginFailure(AuthFailureEvent $event)
    {
        $username = $event->getUsername();

        if (! empty($username)) {
            $this->userLocking->incrementFailedLogin($username);

            if ($this->userLocking->getFailedLogin($username) > BRUTEFORCE_LOCKDOWN) {
                $this->userLocking->lock($username, BRUTEFORCE_LOCKDOWN_DURATION);
            }
        }
    }
}
