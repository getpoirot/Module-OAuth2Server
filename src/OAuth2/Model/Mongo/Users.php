<?php
namespace Module\OAuth2\Model\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;

use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\User as BaseUser;
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
        $e
            ->setUID($user->getUID())
            ->setFullName($user->getFullName())
            ->setIdentifiers($user->getIdentifiers())
            ->setGrants($user->getGrants())
            ->setUsername($user->getUsername())
            ->setPassword($user->getPassword())
            ->setDateCreated( $user->getDateCreated() )
        ;

        $r = $this->_query()->insertOne($e);

        $u = new BaseUser; // Don`t contains specific Repo Entity Model Fields such as date specific
        $u
            ->setUID($e->getUID())
            ->setFullName($e->getFullName())
            ->setIdentifiers($e->getIdentifiers())
            ->setGrants($e->getGrants())
            ->setUsername($user->getUsername())
            ->setPassword($e->getPassword())
            ->setDateCreated($e->getDateCreated())
        ;

        return $u;
    }

    /**
     * Delete Entity By Identifier
     *
     * @param string  $uid
     * @param boolean $validated  Validated Only?
     *
     * @return int Deleted Count
     */
    function deleteByUID($uid, $validated)
    {
        $r = $this->_query()->deleteMany([
            'uid' => $uid
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
     * @param string $uid
     *
     * @return iEntityUser|false
     */
    function findOneByUID($uid)
    {
        $r = $this->_query()->findOne([
            'uid' => (string) $uid,
        ]);

        return $r ? $r : false;
    }

    /**
     * Find User By Combination Of Username/Password (identifier/credential)
     *
     * @param string $username
     * @param string $credential
     *
     * @return iEntityUser|false
     */
    function findOneByUserPass($username, $credential)
    {
        /** @var \MongoDB\Driver\Cursor $r */
        $cursor = $this->_query()->aggregate([
            [
                '$match' => ['identifiers' => [
                    '$elemMatch' => [
                        // iEntityUserIdentifierObject()
                        'validated' => true,
                        'value'     => $username,
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

    /**
     * Update Identifier Type Of Given User to Validated
     *
     * @param string $uid User Identifier
     * @param string $identifierType
     *
     * @return int Affected Rows
     */
    function updateIdentifierAsValidated($uid, $identifierType)
    {
        $r = $this->_query()->updateMany(
            [
                'uid' => $uid,
                'identifiers' => [
                    '$elemMatch' => [
                        'type'  => $identifierType,
                    ],
                ]
            ],
            [
                '$set' => [
                    'identifiers.$.validated' => true,
                ]
            ]
        );

        return $r->getModifiedCount();
    }
    
    
    // Implement iProviderIdentityData:

    /**
     * Finds a user by the given user Identity.
     *
     * @param string $property ie. 'username'
     * @param mixed  $value    ie. 'payam@mail.com'
     *
     * @return iData
     * @throws \Exception
     */
    function findOneMatchBy($property, $value)
    {
        switch ($property) {
            case 'uid':
                return $this->findOneByUID($value);
            case 'username':
                $userIdentifier = new UserIdentifierObject(['type' => 'email', 'value' => $value, 'validated' => true]);
                $user = $this->findOneByIdentifiers( [$userIdentifier] );
                // TODO return iData interface
                return $user;

            default:
                throw new \Exception(sprintf(
                    'Provide Data with (%s) property not Implemented; value given: (%s).'
                    , $property, \Poirot\Std\flatten($value)
                ));
        }
    }
}