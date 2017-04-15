<?php
namespace Module\OAuth2\Services\Grant;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2\Server\Grant\GrantAuthCode;


class ServiceAuthorizationCode
    extends aServiceContainer
{
    /**
     * Create Service
     *
     * @return GrantAuthCode
     */
    function newService()
    {
        $grantType = new GrantAuthCode;
        $grantType
            ->setTtlAuthCode(new \DateInterval('PT5M'))
            ->setTtlRefreshToken(new \DateInterval('P1M'))
            ->setTtlAccessToken(new \DateInterval('PT1H'))

            ->setRepoUser( \Module\OAuth2\Services\Repository\IOC::Users() )
            ->setRepoAuthCode( \Module\OAuth2\Services\Repository\IOC::AuthCodes() )
            ->setRepoRefreshToken( \Module\OAuth2\Services\Repository\IOC::RefreshTokens() )
            ->setRepoAccessToken( \Module\OAuth2\Services\Repository\IOC::AccessTokens() )

            ->setRetrieveUserCallback(
                \Module\OAuth2\Actions\IOC::bareService()->RetrieveAuthenticatedUser
            )
        ;

        return $grantType;
    }
}
