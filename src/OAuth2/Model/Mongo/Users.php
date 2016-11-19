<?php
namespace Module\OAuth2\Model\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;

use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\UserIdentifierObject;
use Poirot\AuthSystem\Authenticate\Interfaces\iProviderIdentityData;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;
use Poirot\Std\Interfaces\Struct\iData;


class Users extends aRepository
    implements iRepoUsers
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
     * Insert User Entity
     *
     * @param \Module\OAuth2\Interfaces\Model\iEntityUser $user
     *
     * @return \Module\OAuth2\Interfaces\Model\iEntityUser
     */
    function insert(\Module\OAuth2\Interfaces\Model\iEntityUser $user)
    {
        $e = new User; // use object model persist
        $e->setIdentifier($user->getIdentifier())
            ->setFullName($user->getFullName())
            ->setIdentifiers($user->getIdentifiers())
            ->setGrants($user->getGrants())
            ->setPassword($user->getPassword())
            ->setDateCreated( $user->getDateCreated() )
        ;

        $r = $this->_query()->insertOne($e);

        // TODO return iEntityUser interface now data returned contains Specific Mongo Object Model
        //$e->set_Id($r->getInsertedId());
        return $e;
    }

    /**
     * Delete Entity By Identifier
     *
     * @param string  $identifier
     * @param boolean $validated  Validated Only?
     *
     * @return int Deleted Count
     */
    function deleteByIdentifier($identifier, $validated)
    {
        $r = $this->_query()->deleteMany([
            'identifiers' => [
                '$elemMatch' => [
                    'type'      => 'email',
                    'value'     => $identifier,
                    'validated' => (boolean) $validated,
                ]
            ]
        ]);

        return $r->getDeletedCount();
    }

    /**
     * Is Identifier Existed?
     *
     * @param []iEntityUserIdentifierObject $identifier
     *
     * @return boolean
     */
    function isIdentifiersRegistered(array $identifiers)
    {
        $or = [];
        /** @var iEntityUserIdentifierObject $arg */
        foreach ($identifiers as $arg)
            $or[] = [ 'type' =>  $arg->getType(), 'value' => $arg->getValue()];


        $query = [
            'identifiers' => [ '$elemMatch' => [
                'validated' => true,
                '$or' => $or,
            ]]
        ];

        $r = $this->_query()->findOne($query);

        return (boolean) $r;
    }

    /**
     * Find Match With Exact Identifiers?
     *
     * @param array   $identifiers
     * @param boolean $allValidated
     *
     * @return iEntityUser|false
     */
    function findOneByIdentifiers(array $identifiers, $allValidated = null)
    {
        $match = [];

        /** @var iEntityUserIdentifierObject $arg */
        foreach ($identifiers as $arg) {
            $match[] = [
                '$match' => ['identifiers' => [
                    '$elemMatch' => [
                        // iEntityUserIdentifierObject()
                        'type'      => $arg->getType(),
                        'value'     => $arg->getValue(),
                        'validated' => ($allValidated !== null) ? $allValidated : $arg->isValidated()
                    ],
                ],],
            ];
        }


        /** @var \MongoDB\Driver\Cursor $r */
        $cursor = $this->_query()->aggregate($match);

        $r = false;
        foreach ($cursor as $r)
            break;

        return $r;
    }
    
    /**
     * Find User By Identifier (username)
     *
     * @param string $identifier
     *
     * @return iEntityUser|false
     */
    function findOneByIdentifier($identifier)
    {
        $id = new UserIdentifierObject;
        $id->setValidated(true);
        $id->setType('email');
        $id->setValue($identifier);

        return $this->findOneByIdentifiers([$id]);
    }

    /**
     * Find User By Combination Of Username/Password (identifier/credential)
     *
     * @param string $userIdentifier
     * @param string $credential
     *
     * @return iEntityUser|false
     */
    function findOneByUserPass($userIdentifier, $credential)
    {
        /** @var \MongoDB\Driver\Cursor $r */
        $cursor = $this->_query()->aggregate([
            [
                '$match' => ['identifiers' => [
                    '$elemMatch' => [
                        // iEntityUserIdentifierObject()
                        'validated' => true,
                        'value'     => $userIdentifier,
                    ],
                ],],
            ],
            [
                '$match' => ['grants' => [
                    '$elemMatch' => [
                        // iEntityUserGrantObject()
                        'type'  => 'password',
                        'value' => md5($credential),
                    ]
                ],],
            ],
            [
                '$limit' => 1,
            ],
        ]);

        $r = false;
        foreach ($cursor as $r)
            break;

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
    function findOneMatchBy($property, $value)
    {
        if ($property !== 'identifier')
            throw new \Exception(sprintf(
                'Data only provide with "identifier" property; given: (%s).'
                , \Poirot\Std\flatten($value)
            ));
        
        return $this->findOneByIdentifier($value);
    }
}
