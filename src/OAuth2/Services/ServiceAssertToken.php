<?php
namespace Module\OAuth2\Services;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2Client\Assertion\AssertByInternalServer;


class ServiceAuthorizeToken
    extends aServiceContainer
{
    const CONF_KEY = 'AuthorizeToken';


    /**
     * Create Service
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
