<?php
namespace Module\OAuth2\Model\Driver\Mongo;

use Module\MongoDriver\Model\tPersistable;
use MongoDB\BSON\Persistable;
use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthClient;


class ClientEntity
    extends \Module\OAuth2\Model\Entity\ClientEntity
    implements iOAuthClient
    , Persistable
{
    use tPersistable;

}
