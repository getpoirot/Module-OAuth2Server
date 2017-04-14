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
    protected $name = BuildServices::AUTHENTICATOR;
    
    
    /**
     * Create Service
     *
     * @return mixed
     */
    function newService()
    {
        ## Set Credential Repo Behalf Of Users Repository
        $repoUsers = $this->services()
            ->get('/module/oauth2/services/repository/'.BuildServices::USERS);

        $credentialAdapter = __(new RepoUserPassCredential)->setRepoUsers($repoUsers);


        ### Attain Login Continue If Has
        /** @var iHttpRequest $request */
        $request  = $this->services()->get('/Request');

        $authenticator = new Authenticator(
            __(new IdentifierWrapIdentityMap(
                // TODO using cookie+session identifier to recognize user and feature to remember me!!
                __(new IdentifierSession)->setIssuerException(function(exAuthentication $e) use ($request) {
                    $loginUrl = (string) \Module\Foundation\Actions\IOC::url('main/oauth/login'); // ensure routes loaded
                    $continue = \Module\Foundation\Actions\IOC::path(sprintf(
                        '$baseUrl/%s'
                        , ltrim($request->getTarget(), '/'))
                    );
                    $loginUrl .= '?continue='.urlencode($continue);
                    header('Location: '.$loginUrl);
                })
                /** @see Users::findOneMatchBy */
                , new IdentityFulfillmentLazy($repoUsers, 'uid')
            ))->setRealm(aIdentifier::DEFAULT_REALM)
            , $credentialAdapter // Identity Username --------^
        );

        return $authenticator;
    }
}
