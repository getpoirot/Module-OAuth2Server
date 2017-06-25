<?php
namespace Module\OAuth2\Services;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2Client\Assertion\AssertByInternalServer;


/**
 * OAuth2Client Settings Can Use:
 *
 * // AssertByInternalServer
 * 'token_assertion' => new \Poirot\Ioc\instance(
 *    '/module/oauth2/services/AssertToken'
 * ),
 */

class ServiceAssertToken
    extends aServiceContainer
{
    /**
     * Assertion Token Directly Connect To DB Repo.
     *
     * @return AssertByInternalServer
     */
    function newService()
    {
        $accessTokens  = $this->services()->get('/module/oauth2/services/repository/AccessTokens');
        $authorizeToen = new AssertByInternalServer($accessTokens);
        return $authorizeToen;
    }
}
