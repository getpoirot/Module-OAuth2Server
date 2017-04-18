<?php
namespace Module\OAuth2\Model\Driver\Mongo;

use Module\OAuth2\Model\Entity;

use Module\MongoDriver\Model\Repository\aRepository;

use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use MongoDB\BSON\ObjectID;
use Poirot\AuthSystem\Authenticate\Interfaces\iProviderIdentityData;
use Poirot\Std\Interfaces\Struct\iData;


class UserRepo
    extends aRepository
    implements iRepoUsers
    , iProviderIdentityData
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        $this->setModelPersist(new UserEntity);
    }


    /**
     * Generate next unique identifier to persist
     * data with
     *
     * @param null|string $id
     *
     * @return mixed
     * @throws \Exception
     */
    function attainNextIdentifier($id = null)
    {
        try {
            $objectId = ($id !== null) ? new ObjectID( (string)$id ) : new ObjectID;
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Invalid Persist (%s) Id is Given.', $id));
        }

        return $objectId;
    }

    /**
     * Used When Persistence want to store credential
     * or match given plain hash with persistence
     *
     * exp. md5(password) = stored_password
     *
     * @param string $credential
     *
     * @return mixed
     */
    function makeCredentialHash($credential)
    {
        return md5($credential);
    }

    
    /**
     * Insert User Entity
     *
     * @param \Module\OAuth2\Interfaces\Model\iOAuthUser $user
     *
     * @return \Module\OAuth2\Interfaces\Model\iOAuthUser
     */
    function insert(\Module\OAuth2\Interfaces\Model\iOAuthUser $user)
    {
        $e = new UserEntity; // use object model persist
        $e
            ->setUid($user->getUid())
            ->setFullName($user->getFullName())
            ->setIdentifiers($user->getIdentifiers())
            ->setGrants($user->getGrants())
            ->setUsername($user->getUsername())
            ->setPassword( $this->makeCredentialHash($user->getPassword()) )
            ->setDateCreated( $user->getDateCreated() )
        ;

        $r = $this->_query()->insertOne($e);

        $u = new Entity\UserEntity; // Don`t contains specific Repo Entity Model Fields such as date specific
        $u
            ->setUid($e->getUid())
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
     * Has Identifier Existed?
     * return identifiers from list that has picked by someone or empty list
     *
     * @param []iEntityUserIdentifierObject $identifier
     *
     * @return []iEntityUserIdentifierObject
     */
    function hasAnyIdentifiersRegistered(array $identifiers)
    {
        $or = [];
        /** @var iUserIdentifierObject $id */
        foreach ($identifiers as $id) {
            if (!$id instanceof iUserIdentifierObject)
                throw new \InvalidArgumentException(sprintf(
                    'Identifier must be instance of "iEntityUserIdentifierObject"; given: (%s).'
                    , \Poirot\Std\flatten($id)
                ));

            $val = $id->getValue();
            if ($val instanceof \Traversable)
                $val = \Poirot\Std\cast($val)->toArray();

            $or[] = [ 'type' =>  $id->getType(), 'value' => $val ];
        }


        $query = [
            'identifiers' => [ '$elemMatch' => [
                '$or' => $or,
            ]]
        ];


        /** @var iOAuthUser|UserEntity $r */
        $r = $this->_query()->findOne($query);
        
        $return = [];
        if ($r) {
            // check which identifiers are picked by user
            /** @var iUserIdentifierObject $uid */
            foreach ($r->getIdentifiers() as $uid) {
                /** @var iUserIdentifierObject $gid */
                foreach ($identifiers as $i => $gid) {
                    if ($uid->getType() != $gid->getType() || $uid->getValue() != $gid->getValue())
                        continue;
                    
                    $return[] = $gid;
                    unset($identifiers[$i]);
                }
            }
        }
        
        return $return;
    }

    /**
     * Find Match With Exact Identifiers?
     *
     * @param iUserIdentifierObject[] $identifiers
     *
     * @return iOAuthUser|false
     */
    function findOneMatchByIdentifiers(array $identifiers)
    {
        $match = [];

        /** @var iUserIdentifierObject $arg */
        foreach ($identifiers as $arg) {
            $match[] = [
                '$match' => ['identifiers' => [
                    '$elemMatch' => \Poirot\Std\cast($arg)->toArray(function($val) {
                            return $val === null; // filter null values
                        })
                        // iEntityUserIdentifierObject()
                        /*
                        'type'      => $arg->getType(),
                        'value'     => $arg->getValue(),
                        'validated' => $arg->isValidated()
                        */
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
     * Find Match With Exact Identifier Value
     *
     * @param string|array|\Traversable $value
     *
     * @return iOAuthUser|false
     */
    function findOneHasIdentifierWithValue($value)
    {
        if ($value instanceof \Traversable)
            // Identifier may is an object
            // exp. Mobile: ['country_code': xx, 'number': xx]
            $value = \Poirot\Std\cast($value)->toArray();

        /** @var iUserIdentifierObject $arg */
            $match[] = [
                '$match' => [],
            ];

        $r = $this->_query()->findOne([
            'identifiers' => [
                '$elemMatch' => [
                    'value'     => $value
                ],
            ],
        ]);

        return $r ? $r : false;
    }
    
    /**
     * Find User By Identifier (username)
     *
     * @param string $uid
     *
     * @return iOAuthUser|false
     */
    function findOneByUID($uid)
    {
        $r = $this->_query()->findOne([
            'uid' => $this->attainNextIdentifier($uid),
        ]);

        return $r ? $r : false;
    }

    /**
     * Find User By Combination Of Username/Password (identifier/credential)
     *
     * @param string $username
     * @param string $credential
     *
     * @return iOAuthUser|false
     */
    function findOneByUserPass($username, $credential)
    {
        /** @var \MongoDB\Driver\Cursor $r */
        $cursor = $this->_query()->aggregate([
            [
                '$match' => ['identifiers' => [
                    '$elemMatch' => [
                        // iEntityUserIdentifierObject()
                        'value'     => $username, // match with any element item if array
                    ],
                ],],
            ],
            [
                '$match' => ['grants' => [
                    '$elemMatch' => [
                        // iEntityUserGrantObject()
                        'type'  => 'password',
                        'value' => $this->makeCredentialHash($credential),
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
    function updateUserIdentifierAsValidated($uid, $identifierType)
    {
        $r = $this->_query()->updateMany(
            [
                'uid' => $this->attainNextIdentifier($uid),
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

    /**
     * Set Identifier Type Of Given User
     *
     * !! delete and add new identifier
     *
     * @param string $uid User Identifier
     * @param string $identifierType
     * @param mixed  $value
     * @param bool   $validated
     *
     * @return int Affected Rows
     */
    function setUserIdentifier($uid, $identifierType, $value, $validated = false)
    {
        $r = $this->_query()->updateMany(
            [
                'uid' => $this->attainNextIdentifier($uid),
            ],
            [
                '$pull' => [
                    'identifiers' => [
                        'type' => $identifierType,
                    ],
                ]
            ]
        );

        $r = $this->_query()->updateMany(
            [
                'uid' => $this->attainNextIdentifier($uid),
            ],
            [
                '$addToSet' => [
                    'identifiers' => [
                        'type'      => $identifierType,
                        'value'     => $value,
                        'validated' => $validated,
                    ],
                ]
            ]
        );

        return $r->getModifiedCount();
    }

    /**
     * Update Specific Grant Type By Given Value
     *
     * !! used to change password or specific credential of user
     *
     * @param string $uid
     * @param string $grantType
     * @param string $grantValue
     *
     * @return int Affected Rows
     */
    function updateGrantTypeValue($uid, $grantType, $grantValue)
    {
        if ($grantType == 'password')
            // All Passwords Stored As MD5Sum
            $grantValue = $this->makeCredentialHash($grantValue);

        $r = $this->_query()->updateMany(
            [
                'uid' => $this->attainNextIdentifier($uid),
                'grants' => [
                    '$elemMatch' => [
                        'type'  => $grantType,
                    ],
                ]
            ],
            [
                '$set' => [
                    'grants.$.value' => $grantValue,
                ]
            ]
        );

        return $r->getModifiedCount();
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
            'uid' => $this->attainNextIdentifier($uid)
        ]);

        return $r->getDeletedCount();
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
            case 'email':
                $userIdentifier = new IdentifierObject(['type' => 'email', 'value' => $value]);
                $user = $this->findOneMatchByIdentifiers( [$userIdentifier] );
                // TODO return iData interface
                return $user;
            case 'username':
                $userIdentifier = new IdentifierObject(['type' => 'username', 'value' => $value]);
                $user = $this->findOneMatchByIdentifiers( [$userIdentifier] );
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