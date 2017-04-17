<?php
namespace Module\OAuth2\Model\Driver\Mongo;

use Module\MongoDriver\Model\tPersistable;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\UTCDatetime;


class UserEntity
    extends \Module\OAuth2\Model\Entity\UserEntity
    implements iOAuthUser
    , Persistable
{
    use tPersistable;

    /**
     * Set Created Date
     *
     * @param UTCDatetime $date
     *
     * @return $this
     */
    function setDateCreatedMongo(UTCDatetime $date)
    {
        $this->setDateCreated($date->toDateTime());
        return $this;
    }

    /**
     * Get Created Date
     * note: persist when serialize
     *
     * @return UTCDatetime
     */
    function getDateCreatedMongo()
    {
        $dateTime = $this->getDateCreated();
        return new UTCDatetime($dateTime->getTimestamp() * 1000);
    }

    /**
     * @override Ignore from persistence
     *
     * Get Created Date
     * @ignore disable when serialized and persist
     *
     * @return \DateTime
     */
    function getDateCreated()
    {
        return parent::getDateCreated();
    }


    // ...

    /**
     * Constructs the object from a BSON array or document
     * Called during unserialization of the object from BSON.
     * The properties of the BSON array or document will be passed to the method as an array.
     * @link http://php.net/manual/en/mongodb-bson-unserializable.bsonunserialize.php
     * @param array $data Properties within the BSON array or document.
     */
    function bsonUnserialize(array $data)
    {
        if (isset($data['identifiers'])) {
            $preIdents = [];
            foreach ($data['identifiers'] as $identifier) {
                if (! $identifier instanceof iUserIdentifierObject ) {
                    if ($identifier instanceof \Traversable)
                        $identifier = \Poirot\Std\cast($identifier)->toArray();

                    $preIdents[] = IdentifierObject::newIdentifierByType(
                        $identifier['type']
                        , $identifier['value']
                        , $identifier['validated']
                    );
                }
            }

            $data['identifiers'] = $preIdents;
        }

        $this->import($data);
    }
}
