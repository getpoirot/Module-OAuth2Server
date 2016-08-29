<?php
namespace Module\OAuth2\Model\Repo\Mongo\Users;

use Module\MongoDriver\Model\Repository\aRepository;
use Module\OAuth2\Interfaces\Server\Repository\iRepoUsersApprovedClients;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityClient;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;


/*
{
   "_id": ObjectId("57c411643587af19008b4567"),
   "user": "naderi.payam@gmail.com",
   "clients_approved": [
     {
         "client": ObjectId("57b96ddd3be2ba000f64d001"),
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
        /*$r = $this->_query()->findOne([
            'identifier' => $identifier,
            'credential' => md5($credential),
        ]);

        return $r;*/
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
        // TODO: Implement approveClient() method.
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
     * @return iEntityClient|false
     */
    function hasApproved(iEntityUser $user, iEntityClient $client)
    {
        // TODO: Implement hasApproved() method.
    }
}
