<?php
namespace Module\OAuth2\Services\Grant;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2\Server\Grant\GrantPassword;


class ServicePassword
    extends aServiceContainer
{
    /**
     * Create Service
     *
     * @return GrantPassword
     */
    function newService()
    {
        $grantType = new GrantPassword;
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
