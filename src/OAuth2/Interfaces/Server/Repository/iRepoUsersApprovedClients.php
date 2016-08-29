<?php
namespace Module\OAuth2\Interfaces\Server\Repository;

use Poirot\OAuth2\Interfaces\Server\Repository\iEntityClient;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;

interface iRepoUsersApprovedClients
{
    /**
     * User Approve Client 
     * 
     * @param iEntityUser   $user
     * @param iEntityClient $client
     * 
     * @return void
     */
    function approveClient(iEntityUser $user, iEntityClient $client);

    /**
     * @param iEntityUser $user
     * @param iEntityClient $client
     * 
     * @return iEntityClient|false
     */
    function hasApproved(iEntityUser $user, iEntityClient $client);
    
    /**
     * User Remove Client Approval
     *
     * @param iEntityUser   $user
     * @param iEntityClient $client
     *
     * @return void
     */
    function removeClient(iEntityUser $user, iEntityClient $client);

    /**
     * List Approved Clients By User
     *
     * @param iEntityUser $user
     *
     * @return
     */
    function listClients(iEntityUser $user);
}
