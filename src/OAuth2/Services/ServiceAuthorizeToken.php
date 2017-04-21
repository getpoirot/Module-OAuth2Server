<?php
namespace Module\OAuth2\Services;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2\Resource\Validation\AuthorizeByInternalServer;


class ServiceAuthorizeToken
    extends aServiceContainer
{
    const CONF_KEY = 'AuthorizeToken';


    /**
     * Create Service
     *
     * @return AuthorizeByInternalServer
     */
    function newService()
    {
        $accessTokens  = $this->services()->get('/module/oauth2/services/repository/AccessTokens');
        $authorizeToen = new AuthorizeByInternalServer($accessTokens);
        return $authorizeToen;
    }
}
