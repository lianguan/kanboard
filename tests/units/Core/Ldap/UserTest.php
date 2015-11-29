<?php

namespace Kanboard\Core\Ldap;

require_once __DIR__.'/../../Base.php';

class UserTest extends \Base
{
    public function testGetProfile()
    {
        $entries = array(
            'count' => 1,
            0 => array(
                'count' => 2,
                'dn' => 'uid=my_user,ou=People,dc=kanboard,dc=local',
                'displayname' => array(
                    'count' => 1,
                    0 => 'My LDAP user',
                ),
                'mail' => array(
                    'count' => 2,
                    0 => 'user1@localhost',
                    1 => 'user2@localhost',
                ),
                'samaccountname' => array(
                    'count' => 1,
                    0 => 'my_ldap_user',
                ),
                0 => 'displayname',
                1 => 'mail',
                2 => 'samaccountname',
            )
        );

        $expected = array(
            'ldap_id' => 'uid=my_user,ou=People,dc=kanboard,dc=local',
            'username' => 'my_ldap_user',
            'name' => 'My LDAP user',
            'email' => 'user1@localhost',
            'is_admin' => 0,
            'is_project_admin' => 0,
            'is_ldap_user' => 1,
        );

        $client = $this
            ->getMockBuilder('\Kanboard\Core\Ldap\Client')
            ->setMethods(array(
                'getConnection',
            ))
            ->getMock();

        $client
            ->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue('my_ldap_resource'));

        $query = $this
            ->getMockBuilder('\Kanboard\Core\Ldap\Query')
            ->setConstructorArgs(array($client, $entries))
            ->setMethods(array(
                'execute',
                'hasResult',
            ))
            ->getMock();

        $query
            ->expects($this->once())
            ->method('execute')
            ->with(
                $this->equalTo('ou=People,dc=kanboard,dc=local'),
                $this->equalTo('(uid=my_user)')
            );

        $query
            ->expects($this->once())
            ->method('hasResult')
            ->will($this->returnValue(true));

        $user = $this
            ->getMockBuilder('\Kanboard\Core\Ldap\User')
            ->setConstructorArgs(array($query))
            ->setMethods(array(
                'getAttributeUsername',
                'getAttributeEmail',
                'getAttributeName',
            ))
            ->getMock();

        $user
            ->expects($this->any())
            ->method('getAttributeUsername')
            ->will($this->returnValue('samaccountname'));

        $user
            ->expects($this->any())
            ->method('getAttributeName')
            ->will($this->returnValue('displayname'));

        $user
            ->expects($this->any())
            ->method('getAttributeEmail')
            ->will($this->returnValue('mail'));

        $this->assertEquals($expected, $user->find('ou=People,dc=kanboard,dc=local', '(uid=my_user)'));
    }
}
