<?php
namespace Module\OAuth2\Model\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;
use Module\OAuth2\Interfaces\Model\iEntityValidationCode;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use MongoDB\BSON\UTCDatetime;


/**
 * Note: Using TTL Index On "date_mongo_expiration"
 *       db.oauth.users.validation_codes.createIndex({"date_mongo_expiration": 1}, {expireAfterSeconds: 0});
 *
 */
class ValidationCodes extends aRepository
    implements iRepoValidationCodes
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        $this->setModelPersist(new ValidationCode);
    }

    /**
     * Insert Validation Code
     *
     * note: each user must has one validation code persistence at time
     *       "user_identifier" is unique
     *
     * @param iEntityValidationCode $validationCode
     *
     * @return iEntityValidationCode
     */
    function insert(iEntityValidationCode $validationCode)
    {
        $e = new ValidationCode; // use object model persist
        $e  ->setUserIdentifier($validationCode->getUserIdentifier())
            ->setValidationCode($validationCode->getValidationCode())
            ->setAuthCodes($validationCode->getAuthCodes())
            ->setDateTimeExpiration($validationCode->getDateTimeExpiration())
            ->setContinueFollowRedirection($validationCode->getContinueFollowRedirection())
        ;

        $r = $this->_query()->insertOne($e);

        // TODO return iEntityValidationCode interface now data returned contains Specific Mongo Object Model
        return $e;
    }

    /**
     * Find Match By Given Validation Code
     *
     * note: consider expiration time
     *
     * @param string $validationCode
     *
     * @return iEntityValidationCode|false
     */
    function findOneByValidationCode($validationCode)
    {
        $r = $this->_query()->findOne([
            'validation_code'       => $validationCode,
            /// there may be a delay between the time a document expires and the time
            //- that MongoDB removes the document from the database.
            /* Disabled To Avoid Using Compound Indexes
             * 'date_mongo_expiration' => [
                '$lte' => new UTCDatetime( round(microtime(true) * 1000) ),
            ]*/
        ]);

        return $r ? $r : false;
    }

    /**
     * Find Match By Given User Identifier
     *
     * note: consider expiration time
     *
     * @param string $userIdentifier
     *
     * @return iEntityValidationCode|false
     */
    function findOneByUserIdentifier($userIdentifier)
    {
        $r = $this->_query()->findOne([
            'user_identifier'       => $userIdentifier,
            /// there may be a delay between the time a document expires and the time
            //- that MongoDB removes the document from the database.
            /* Disabled To Avoid Using Compound Indexes
             * 'date_mongo_expiration' => [
                '$lte' => new UTCDatetime( round(microtime(true) * 1000) ),
            ]*/
        ]);

        return $r ? $r : false;
    }

    /**
     * Delete Entity By Given Validation Code
     *
     * @param string $validationCode
     *
     * @return int Deleted Count
     */
    function deleteByValidationCode($validationCode)
    {
        $r = $this->_query()->deleteMany([
            'validation_code' => $validationCode
        ]);

        return $r->getDeletedCount();
    }

    /**
     * Update Authorization Type Of Given Validation Code
     * to Validated
     *
     * @param string $validationCode
     * @param string $authType
     *
     * @return int Affected Rows
     */
    function updateAuthCodeAsValidated($validationCode, $authType)
    {
        $r = $this->_query()->updateMany(
            [
                'validation_code' => $validationCode,
                'auth_codes.type' => $authType,
            ],
            [
                '$set' => [
                    'auth_codes.$.validated' => true,
                ]
            ]
        );

        return $r->getModifiedCount();
    }

    /**
     * Update Sent DateTime Data Of AuthCode Type From Given Validation Code
     * To Current Time
     *
     * @param string $validationCode
     * @param string $authType
     *
     * @return int Affected Rows
     */
    function updateAuthCodeTimestampSent($validationCode, $authType)
    {
        $r = $this->_query()->updateMany(
            [
                'validation_code' => $validationCode,
                'auth_codes.type' => $authType,
            ],
            [
                '$set' => [
                    'auth_codes.$.timestamp_sent' => __(new UTCDatetime(time()))->__toString(),
                ]
            ]
        );

        return $r->getModifiedCount();
    }
}
