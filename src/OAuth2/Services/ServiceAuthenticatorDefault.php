<?php
namespace Module\OAuth2\Services;

use Module\OAuth2\Model\Authenticate\IdentityCredentialDigestRepoUser;
use Poirot\AuthSystem\Authenticate\Authenticator;
use Poirot\AuthSystem\Authenticate\Identifier\IdentifierBasicAuth;
use Poirot\AuthSystem\Authenticate\Identifier\IdentifierWrapIdentityMap;
use Poirot\AuthSystem\Authenticate\Identity\IdentityFulfillmentLazy;
use Poirot\Ioc\Container\Service\aServiceContainer;


class ServiceAuthenticatorDefault
    extends aServiceContainer
{
    protected $name = BuildOAuthModuleServices::SERVICE_NAME_AUTHENTICATOR;
    
    
    /**
     * Create Service
     *
     * @return mixed
     */
    function newService()
    {
        $repoUsers = $this->services()
            ->get('/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_USERS);
        $credentialAdapter = __(new IdentityCredentialDigestRepoUser())->setRepoUsers($repoUsers);

        $authenticator = new Authenticator(
            __(new IdentifierWrapIdentityMap(
                __(new IdentifierBasicAuth())->setCredentialAdapter($credentialAdapter)
                , new IdentityFulfillmentLazy($repoUsers, 'username')
            ))->setRealm(\Poirot\AuthSystem\Authenticate\Identifier\aIdentifier::DEFAULT_REALM)
            , $credentialAdapter
        );

        return $authenticator;
    }
}
