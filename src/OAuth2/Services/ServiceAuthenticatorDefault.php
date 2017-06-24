<?php
namespace Module\OAuth2\Services;

use Module\Authorization\Services\ContainerAuthenticatorsCapped;
use Module\OAuth2\Model\Authenticate\RepoUserPassCredential;
use Poirot\AuthSystem\Authenticate\Authenticator;
use Poirot\AuthSystem\Authenticate\Exceptions\exAuthentication;
use Poirot\AuthSystem\Authenticate\Identifier\aIdentifier;
use Poirot\AuthSystem\Authenticate\Identifier\IdentifierSession;
use Poirot\AuthSystem\Authenticate\Identifier\IdentifierWrapIdentityMap;
use Poirot\AuthSystem\Authenticate\Identity\IdentityFulfillmentLazy;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Ioc\Container\Service\aServiceContainer;


/**
 * Authenticator Service That Register in Module Authorize as
 * authenticators capped plugin.
 *
 */
class ServiceAuthenticatorDefault
    extends aServiceContainer
{
    protected $name = \Module\OAuth2\Module::REALM;
    
    
    /**
     * Create Service
     *
     * @return Authenticator
     */
    function newService()
    {
        ## Set Credential Repo Behalf Of Users Repository
        $repoUsers = \Module\OAuth2\Services\Repository\IOC::Users();
        $credentialAdapter = __(new RepoUserPassCredential)->setRepoUsers($repoUsers);


        ### Attain Login Continue If Has
        /** @var iHttpRequest $request */
        $request  = \IOC::GetIoC()->get('/HttpRequest');

        $authenticator = new Authenticator(
            __(new IdentifierWrapIdentityMap(
                // TODO using cookie+session identifier to recognize user and feature to remember me!!
                __(new IdentifierSession)->setIssuerException(function(exAuthentication $e) use ($request) {
                    $loginUrl = \Module\HttpFoundation\Actions::url('main/oauth/login'); // ensure routes loaded
                    $continue = \Module\Foundation\Actions::path(sprintf(
                        '$baseUrl/%s'
                        , ltrim($request->getTarget(), '/'))
                    );
                    $loginUrl = \Poirot\Psr7\modifyUri( $loginUrl->uri(), [ 'query' => ['continue' => $continue] ] );
                    header('Location: '.$loginUrl);
                })
                /** @see Users::findOneMatchBy */
                , new IdentityFulfillmentLazy($repoUsers, 'uid')
            ))->setRealm(aIdentifier::DEFAULT_REALM)
            , $credentialAdapter // Identity Username --------^
        );

        return $authenticator;
    }

    /**
     * @override
     * !! Access Only In Capped Collection; No Nested Containers Here
     *
     * Get Service Container
     *
     * @return ContainerAuthenticatorsCapped
     */
    function services()
    {
        return parent::services();
    }
}
