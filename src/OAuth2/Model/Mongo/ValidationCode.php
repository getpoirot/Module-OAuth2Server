<?php
namespace Module\OAuth2\Model\Mongo;

use MongoDB\BSON\Persistable;
use MongoDB\BSON\UTCDatetime;

class ValidationCode
    extends \Module\OAuth2\Model\ValidationCode
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
    function setExpirationDateMongo(UTCDatetime $date)
    {
        $this->setExpirationDateTime($date->toDateTime());
        return $this;
    }

    /**
     * Get Created Date
     * note: persist when serialize
     *
     * @return UTCDatetime
     */
    function getExpirationDateMongo()
    {
        $dateTime = $this->getExpirationDateTime();
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
    function getExpirationDateTime()
    {
        return parent::getExpirationDateTime();
    }
}
