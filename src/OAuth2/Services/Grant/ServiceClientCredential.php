<?php
namespace Module\OAuth2\Services\Grant;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2\Server\Grant\GrantClientCredentials;


class ServiceClientCredential
    extends aServiceContainer
{
    /**
     * Create Service
     *
     * @return GrantClientCredentials
     */
    function newService()
    {
        $grantType = new GrantClientCredentials;
        $grantType
            ->setTtlAccessToken(new \DateInterval('PT1H'))

            ->setRepoAccessToken( \Module\OAuth2\Services\Repository\IOC::AccessTokens() )
        ;

        return $grantType;
    }
}
