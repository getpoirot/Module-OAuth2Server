<?php
namespace Module\OAuth2\Services\Grant;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2\Server\Grant\GrantAuthCode;


class ServiceAuthorizationCode
    extends aServiceContainer
{
    protected $ttlAuthCode;
    protected $ttlRefreshToken;
    protected $ttlAccessToken;
    protected $repoAccessToken;


    /**
     * Create Service
     *
     * @return GrantAuthCode
     */
    function newService()
    {
        $grantType = new GrantAuthCode;
        $grantType
            ->setTtlAuthCode( $this->ttlAuthCode )
            ->setTtlRefreshToken( $this->ttlRefreshToken )
            ->setTtlAccessToken( $this->ttlAccessToken )

            ->setRepoUser( \Module\OAuth2\Services\Repository\IOC::Users() )
            ->setRepoClient( \Module\OAuth2\Services\Repository\IOC::Clients() )
            ->setRepoAuthCode( \Module\OAuth2\Services\Repository\IOC::AuthCodes() )
            ->setRepoRefreshToken( \Module\OAuth2\Services\Repository\IOC::RefreshTokens() )
            ->setRepoAccessToken( ($this->repoAccessToken) ? $this->repoAccessToken: \Module\OAuth2\Services\Repository\IOC::AccessTokens() )

            ->setRetrieveUserCallback(
                \Module\OAuth2\Actions\IOC::bareService()->RetrieveAuthenticatedUser
            )
        ;

        return $grantType;
    }


    // ..

    /**
     * @param mixed $ttlAuthCode
     */
    function setTtlAuthCode($ttlAuthCode)
    {
        // new \DateInterval('PT5M')
        $this->ttlAuthCode = $ttlAuthCode;
    }

    /**
     * @param mixed $ttlRefreshToken
     */
    function setTtlRefreshToken($ttlRefreshToken)
    {
        // new \DateInterval('P1M')
        $this->ttlRefreshToken = $ttlRefreshToken;
    }

    /**
     * @param mixed $ttlAccessToken
     */
    function setTtlAccessToken($ttlAccessToken)
    {
        // new \DateInterval('PT1H')
        $this->ttlAccessToken = $ttlAccessToken;
    }

    /**
     * To Generate Different Type Of Token
     *
     * @param mixed $repoAccessToken
     */
    function setRepoAccessToken($repoAccessToken)
    {
        // \Module\OAuth2\Services\Repository\IOC::AccessTokens()
        $this->repoAccessToken = $repoAccessToken;
    }
}
