<?php
namespace Module\OAuth2\Model\Repo\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;

use Module\OAuth2\Model\User;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoUser;


class Users extends aRepository
    implements iRepoUser
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        $this->setModelPersist(new User);
    }

    
    // Implements iRepoUser:
    
    /**
     * Find User By Identifier
     *
     * @param string|int $identifier
     *
     * @return iEntityUser|false
     */
    function findByIdentifier($identifier)
    {
        $r = $this->_query()->findOne([
            'username' => $identifier,
        ]);

        return $r;
    }

    /**
     * Find User By Combination Of Username/Password (identifier/credential)
     *
     * @param string|int $identifier
     * @param string $credential
     *
     * @return iEntityUser|false
     */
    function findByUserCredential($identifier, $credential)
    {
        $r = $this->_query()->findOne([
            'username'   => $identifier,
            'credential' => md5($credential),
        ]);

        return $r;
    }
}
