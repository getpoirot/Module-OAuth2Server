<?php
namespace Module\OAuth2\Model\Driver\Mongo;

use Module\MongoDriver\Model\tPersistable;
use Module\OAuth2\Interfaces\Model\iValidationAuthCodeObject;
use Module\OAuth2\Model\Entity\Validation\AuthObject;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\UTCDatetime;


class ValidationEntity
    extends \Module\OAuth2\Model\Entity\ValidationEntity
    implements Persistable
{
    use tPersistable;

    /**
     * Set Created Date
     *
     * @param UTCDatetime $date
     *
     * @return $this
     */
    function setDateMongoExpiration(UTCDatetime $date)
    {
        $this->setDateTimeExpiration($date->toDateTime());
        return $this;
    }

    /**
     * Get Created Date
     * note: persist when serialize
     *
     * @return UTCDatetime
     */
    function getDateMongoExpiration()
    {
        $dateTime = $this->getDateTimeExpiration();
        return new UTCDatetime($dateTime->getTimestamp() * 1000);
    }

    /**
     * @override Ignore from persistence
     *
     * Expiration DateTime
     * @ignore
     *
     * @return \DateTime
     */
    function getDateTimeExpiration()
    {
        return parent::getDateTimeExpiration();
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
        if (isset($data['auth_codes'])) {
            $preIdents = [];
            foreach ($data['auth_codes'] as $identifier) {
                if (! $identifier instanceof iValidationAuthCodeObject ) {
                    if ($identifier instanceof \Traversable)
                        $identifier = \Poirot\Std\cast($identifier)->toArray();

                    $pre = AuthObject::newIdentifierByType(
                        $identifier['type']
                        , $identifier['value']
                        , $identifier['validated']
                    );

                    $pre->setCode($identifier['code']);

                    if (isset($identifier['timestamp_sent']))
                        $pre->setTimestampSent($identifier['timestamp_sent']);

                    $preIdents[] = $pre;
                }
            }

            $data['auth_codes'] = $preIdents;
        }

        if (isset($data['meta'])) {
            $meta = $data['meta'];
            if ($meta instanceof \Traversable)
                $meta = \Poirot\Std\cast($meta)->toArray();

            $data['meta'] = $meta;
        }

        $this->import($data);
    }
}
