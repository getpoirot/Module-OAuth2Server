<?php
namespace Module\OAuth2\Services;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2Client\Assertion\AssertByInternalServer;


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
