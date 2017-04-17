<?php
namespace Module\OAuth2\Model\Driver\Mongo;

use Module\MongoDriver\Model\tPersistable;
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
}
