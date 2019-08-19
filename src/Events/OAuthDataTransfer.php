<?php
namespace Module\OAuth2\Events;

use Module\OAuth2\Model\Entity\UserEntity;


class OAuthDataTransfer
    extends \Poirot\Events\Event\DataCollector
{
    /** @var UserEntity*/
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
