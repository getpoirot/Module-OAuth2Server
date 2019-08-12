<?php
namespace Module\OAuth2\Events;

use Module\OAuth2\Model\Entity\UserEntity;
use Poirot\Events\Event;


class EventHeapOfOAuth
    extends \Poirot\Events\EventHeap
{
    const USER_REGISTER_BEFORE = 'oauth2.user.register.before';
    const USER_REGISTER        = 'oauth2.user.register';


    /**
     * Initialize
     *
     */
    function __init()
    {
        $this->collector = new DataCollector;

        // attach default event names:
        $this->bind( new Event(self::USER_REGISTER_BEFORE) );
        $this->bind( new Event(self::USER_REGISTER) );
    }

    /**
     * @override ide auto info
     * @inheritdoc
     *
     * @return DataCollector
     */
    function collector($options = null)
    {
        return parent::collector($options);
    }
}

class DataCollector
    extends \Poirot\Events\Event\DataCollector
{
    protected $entityUser;


    function getEntityUser()
    {
        return $this->entityUser;
    }

    function setEntityUser(UserEntity $entityUser)
    {
        $this->entityUser = $entityUser;
    }
}
