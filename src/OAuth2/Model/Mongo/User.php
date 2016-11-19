<?php
namespace Module\OAuth2\Model\Mongo;

use Module\OAuth2\Interfaces\Model\iEntityUser;
use MongoDB\BSON\Persistable;
use MongoDB\BSON\UTCDatetime;


class User extends \Module\OAuth2\Model\User
    implements iEntityUser
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
}
