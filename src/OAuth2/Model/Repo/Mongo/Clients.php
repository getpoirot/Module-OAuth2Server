<?php
namespace Module\OAuth2\Model\Repo\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;
use Module\OAuth2\Model\Client;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityClient;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoClient;


class Clients extends aRepository
    implements iRepoClient
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        $this->setModelPersist(new Client);
    }
    
    /**
     * Insert New Client
     *
     * @param iEntityClient $client
     *
     * @return iEntityClient include insert id
     * @throws \Exception
     */
    function insert(iEntityClient $client)
    {
        $r = $this->_query()->insertOne($client);
        $client->setIdentifier($r->getInsertedId());

        return $client;
    }

    /**
     * Find Client By Identifier
     *
     * @param string|int $clientID
     *
     * @return iEntityClient|false
     */
    function findByIdentifier($clientID)
    {
        $r = $this->_query()->findOne([
            '_id' => new \MongoDB\BSON\ObjectID($clientID),
        ]);

        return $r;
    }

    /**
     * Find Client By Combination Of ID/Secret
     *
     * ! clients must be authenticated by which method contract
     *   between client and server.
     *   by which id/secret validation is default.
     *
     * @param string|int $clientID
     * @param string $secretKey
     *
     * @return iEntityClient|false
     */
    function findByIDSecretKey($clientID, $secretKey)
    {
        $r = $this->_query()->findOne([
            '_id'        => new \MongoDB\BSON\ObjectID($clientID),
            'secret_key' => $secretKey,
        ]);

        return $r;
    }
}
