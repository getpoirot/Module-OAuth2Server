<?php
namespace Module\OAuth2\Services\Grant;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2\Server\Grant\GrantRefreshToken;


class ServiceRefreshToken
    extends aServiceContainer
{
    /**
     * Create Service
     *
     * @return GrantRefreshToken
     */
    function newService()
    {
        $grantType = new GrantRefreshToken;
        $grantType
            ->setTtlRefreshToken(new \DateInterval('P1M'))
            ->setTtlAccessToken(new \DateInterval('PT1H'))

            ->setRepoClient( \Module\OAuth2\Services\Repository\IOC::Clients() )
            ->setRepoUser( \Module\OAuth2\Services\Repository\IOC::Users() )
            ->setRepoRefreshToken( \Module\OAuth2\Services\Repository\IOC::RefreshTokens() )
            ->setRepoAccessToken( \Module\OAuth2\Services\Repository\IOC::AccessTokens() )
        ;

        return $grantType;
    }
}
