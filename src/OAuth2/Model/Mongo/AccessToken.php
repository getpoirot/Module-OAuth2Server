<?php
namespace Module\OAuth2\Model\Mongo;

use MongoDB\BSON\Persistable;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class AccessToken
    extends \Poirot\OAuth2\Model\AccessToken
    implements iEntityAccessToken
    , Persistable
{
    use tPersistable;
}
