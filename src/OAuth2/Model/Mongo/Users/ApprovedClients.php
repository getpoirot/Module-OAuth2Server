<?php
namespace Module\OAuth2\Model\Repo\Mongo\Users;

use Module\MongoDriver\Model\Repository\aRepository;
use Module\OAuth2\Interfaces\Server\Repository\iRepoUsersApprovedClients;
use MongoDB\Model\BSONDocument;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityClient;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;


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
     * @param iEntityUser $user
     *
     * @return
     */
    function listClients(iEntityUser $user)
    {

    }

    /**
     * User Approve Client
     *
     * @param iEntityUser $user
     * @param iEntityClient $client
     *
     * @return void
     */
    function approveClient(iEntityUser $user, iEntityClient $client)
    {
        $r = $this->_query()->findOneAndUpdate(
            [
                'user_identifier' => $user->getIdentifier(),
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
     * @param iEntityUser $user
     * @param iEntityClient $client
     *
     * @return void
     */
    function removeClient(iEntityUser $user, iEntityClient $client)
    {
        // TODO: Implement removeClient() method.
    }

    /**
     * @param iEntityUser $user
     * @param iEntityClient $client
     *
     * @return boolean
     */
    function isUserApprovedClient(iEntityUser $user, iEntityClient $client)
    {
        $userIdentifier   = $user->getIdentifier();
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
