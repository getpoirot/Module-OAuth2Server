<?php
namespace Module\OAuth2\Model\Repo\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;

use Module\OAuth2\Model\User;
use Poirot\AuthSystem\Authenticate\Interfaces\iProviderIdentityData;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoUser;
use Poirot\Std\Interfaces\Struct\iData;


class Users extends aRepository
    implements iRepoUser
    , iProviderIdentityData
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
     * Find User By Identifier (username)
     *
     * @param string $identifier
     *
     * @return iEntityUser|false
     */
    function findByIdentifier($identifier)
    {
        $r = $this->_query()->findOne([
            'identifier' => $identifier,
        ]);

        return $r;
    }

    /**
     * Find User By Combination Of Username/Password (identifier/credential)
     *
     * @param string $identifier
     * @param string $credential
     *
     * @return iEntityUser|false
     */
    function findByUserCredential($identifier, $credential)
    {
        $r = $this->_query()->findOne([
            'identifier' => $identifier,
            'credential' => md5($credential),
        ]);

        return $r;
    }
    
    
    // Implement iProviderIdentityData:

    /**
     * Finds a user by the given user Identity.
     *
     * @param string $property ie. 'user_name'
     * @param mixed $value ie. 'payam@mail.com'
     *
     * @return iData
     * @throws \Exception
     */
    function findBy($property, $value)
    {
        if ($property !== 'identifier')
            throw new \Exception(sprintf(
                'Data only provide with "identifier" property; given: (%s).'
                , \Poirot\Std\flatten($value)
            ));
        
        return $this->findByIdentifier($value);
    }
}
