<?php
namespace Module\OAuth2\Model\Mongo\Users;

use Module\MongoDriver\Model\Repository\aRepository;
use Module\OAuth2\Interfaces\Server\Repository\iRepoUsersApprovedClients;
use MongoDB\Model\BSONDocument;
use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthClient;
use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthUser;


/*
{
   "user_identifier": "naderi.payam@gmail.com",
   "clients_approved": [
     {
         "client_identifier": "57b96ddd3be2ba000f64d001",
         "name": "Anar Filter Service"
    }
  ]
}
*/

class ApprovedClients
    extends aRepository
    implements iRepoUsersApprovedClients
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        // $this->setModelPersist(new User);
    }

    /**
     * List Approved Clients By User
     *
     * @param iOAuthUser $user
     *
     * @return
     */
    function listClients(iOAuthUser $user)
    {

    }

    /**
     * User Approve Client
     *
     * @param iOAuthUser $user
     * @param iOAuthClient $client
     *
     * @return void
     */
    function approveClient(iOAuthUser $user, iOAuthClient $client)
    {
        $r = $this->_query()->findOneAndUpdate(
            [
                'user_identifier' => $user->getUID(),
            ]
            , [
                '$addToSet' => [
                    'clients_approved' => new BSONDocument([
                        'client_identifier' => $client->getIdentifier(),
                        'name'              => $client->getName(),
                    ]),
                ]
            ]
            , [
                'upsert' => true,
            ]
        );
    }

    /**
     * User Remove Client Approval
     *
     * @param iOAuthUser $user
     * @param iOAuthClient $client
     *
     * @return void
     */
    function removeClient(iOAuthUser $user, iOAuthClient $client)
    {
        // TODO: Implement removeClient() method.
    }

    /**
     * @param iOAuthUser $user
     * @param iOAuthClient $client
     *
     * @return boolean
     */
    function isUserApprovedClient(iOAuthUser $user, iOAuthClient $client)
    {
        $userIdentifier   = $user->getUID();
        $clientIdentifier = $client->getIdentifier();
        
        $r = $this->_query()->findOne([
            'user_identifier' => $userIdentifier,
            'clients_approved' => [
                '$elemMatch' => [ "client_identifier" => $clientIdentifier ]
            ]
        ]);
        
        return (boolean) $r;
    }
}
