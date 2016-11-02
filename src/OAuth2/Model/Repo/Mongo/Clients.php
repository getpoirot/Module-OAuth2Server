<?php
namespace Module\OAuth2\Model\Repo\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;
use Module\OAuth2\Model\Client;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityClient;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoClients;


class Clients extends aRepository
    implements iRepoClients
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
        $clientInsert = new Client();
        $clientInsert
            ->setIdentifier($client->getIdentifier())
            ->setClientType($client->getClientType())
            ->setName($client->getName())
            ->setDescription($client->getDescription())
            ->setImage($client->getImage())
            ->setSecretKey($client->getSecretKey())
            ->setOwnerIdentifier($client->getOwnerIdentifier())
            ->setScope($client->getScope())
            ->setRedirectUri($client->getRedirectUri())
            ->setResidentClient($client->isResidentClient())
        ;
        
        $r = $this->_query()->insertOne($client);
        
        $clientInsert->setIdentifier($r->getInsertedId());
        return $clientInsert;
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
            'identifier' => $clientID,
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
            'identifier' => $clientID,
            'secret_key' => $secretKey,
        ]);

        return $r;
    }
}
