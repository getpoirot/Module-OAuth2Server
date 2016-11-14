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

    /** @var UTCDatetime */
    protected $date_created_mongo;


    /**
     * Set Created Date
     *
     * @param UTCDatetime $date
     *
     * @return $this
     */
    function setDateCreatedMongo(UTCDatetime $date)
    {
        $this->date_created_mongo = $date;
        return $this;
    }

    /**
     * Get Created Date
     *
     * @return UTCDatetime
     */
    function getDateCreatedMongo()
    {
        if ($this->date_created_mongo === null)
            $this->setDateCreatedMongo(new UTCDatetime(time() * 1000)); // now

        return $this->date_created_mongo;
    }

    /**
     * Set Created Date
     *
     * @param \DateTime $date
     *
     * @return $this
     */
    function setDateCreated(\DateTime $date)
    {
        return $this->setDateCreatedMongo(new UTCDatetime($date->getTimestamp() * 1000));
    }

    /**
     * Get Created Date
     * @ignore disable when serialized
     *
     * @return \DateTime
     */
    function getDateCreated()
    {
        return $this->getDateCreatedMongo()->toDateTime();
    }
}
