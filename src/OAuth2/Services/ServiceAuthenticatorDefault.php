<?php
namespace Module\OAuth2\Services;

use Module\OAuth2\Model\Authenticate\RepoUserPassCredential;
use Poirot\AuthSystem\Authenticate\Authenticator;
use Poirot\AuthSystem\Authenticate\Exceptions\exAuthentication;
use Poirot\AuthSystem\Authenticate\Identifier\aIdentifier;
use Poirot\AuthSystem\Authenticate\Identifier\IdentifierSession;
use Poirot\AuthSystem\Authenticate\Identifier\IdentifierWrapIdentityMap;
use Poirot\AuthSystem\Authenticate\Identity\IdentityFulfillmentLazy;
use Poirot\Http\Interfaces\iHttpRequest;
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
        $credentialAdapter = __(new RepoUserPassCredential)->setRepoUsers($repoUsers);

        ### Attain Login Continue If Has
        /** @var iHttpRequest $request */
        $request  = $this->services()->get('/Request');

        $authenticator = new Authenticator(
            __(new IdentifierWrapIdentityMap(
                __(new IdentifierSession)->setIssuerException(function(exAuthentication $e) use ($request) {
                    $loginUrl = (string) \Module\Foundation\Actions\IOC::url('main/oauth/login'); // ensure routes loaded
                    $loginUrl .= '?continue='.urlencode($request->getTarget());
                    header('Location: '.$loginUrl);
                })
                , new IdentityFulfillmentLazy($repoUsers, 'username')
            ))->setRealm(aIdentifier::DEFAULT_REALM)
            , $credentialAdapter // Identity Username --------^
        );

        return $authenticator;
    }
}
