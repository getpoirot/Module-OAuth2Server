<?php
namespace Module\OAuth2\Model\Mongo;

use MongoDB\BSON\Persistable;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityClient;


class Client
    extends \Module\OAuth2\Model\Client
    implements iEntityClient
    , Persistable
{
    use tPersistable;

}
