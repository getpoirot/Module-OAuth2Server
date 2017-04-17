<?php
namespace Module\OAuth2\Model\Mongo;

use MongoDB\BSON\Persistable;
use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthClient;


class Client
    extends \Module\OAuth2\Model\ClientEntity
    implements iOAuthClient
    , Persistable
{
    use tPersistable;

}
