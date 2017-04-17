<?php
namespace Module\OAuth2\Model\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;

use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthClient;
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
     * @param iOAuthClient $client
     *
     * @return iOAuthClient include insert id
     * @throws \Exception
     */
    function insert(iOAuthClient $client)
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
     * @return iOAuthClient|false
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
     * @return iOAuthClient|false
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
