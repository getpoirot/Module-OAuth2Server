<?php
namespace Module\OAuth2\Interfaces\Server\Repository;

use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthClient;
use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthUser;

interface iRepoUsersApprovedClients
{
    /**
     * User Approve Client 
     * 
     * @param iOAuthUser   $user
     * @param iOAuthClient $client
     * 
     * @return void
     */
    function approveClient(iOAuthUser $user, iOAuthClient $client);

    /**
     * @param iOAuthUser $user
     * @param iOAuthClient $client
     * 
     * @return boolean
     */
    function isUserApprovedClient(iOAuthUser $user, iOAuthClient $client);
    
    /**
     * User Remove Client Approval
     *
     * @param iOAuthUser   $user
     * @param iOAuthClient $client
     *
     * @return void
     */
    function removeClient(iOAuthUser $user, iOAuthClient $client);

    /**
     * List Approved Clients By User
     *
     * @param iOAuthUser $user
     *
     * @return
     */
    function listClients(iOAuthUser $user);
}
