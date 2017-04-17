<?php
namespace Module\OAuth2\Model\Mongo;

use MongoDB\BSON\Persistable;
use MongoDB\BSON\UTCDatetime;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityRefreshToken;


class RefreshToken
    extends \Poirot\OAuth2\Model\RefreshToken
    implements iEntityRefreshToken
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
     * @ignore
     *
     * @inheritdoc
     */
    function getDateTimeExpiration()
    {
        return parent::getDateTimeExpiration();
    }
}
