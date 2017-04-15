<?php
namespace Module\OAuth2\Services\Grant;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2\Server\Grant\GrantImplicit;


class ServiceImplicit
    extends aServiceContainer
{
    /**
     * Create Service
     *
     * @return GrantImplicit
     */
    function newService()
    {
        $grantType = new GrantImplicit;
        $grantType
            ->setTtlAccessToken(new \DateInterval('PT1H'))

            ->setRepoAccessToken( \Module\OAuth2\Services\Repository\IOC::AccessTokens() )

            ->setRetrieveUserCallback(
                \Module\OAuth2\Actions\IOC::bareService()->RetrieveAuthenticatedUser
            )
        ;

        return $grantType;
    }
}
