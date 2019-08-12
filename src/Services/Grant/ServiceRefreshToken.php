<?php
namespace Module\OAuth2\Services\Grant;

use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2\Server\Grant\GrantRefreshToken;


class ServiceRefreshToken
    extends aServiceContainer
{
    protected $ttlRefreshToken;
    protected $ttlAccessToken;
    protected $repoAccessToken;


    /**
     * Create Service
     *
     * @return GrantRefreshToken
     */
    function newService()
    {
        $grantType = new GrantRefreshToken;
        $grantType
            ->setTtlRefreshToken( $this->ttlRefreshToken )
            ->setTtlAccessToken( $this->ttlAccessToken )

            ->setRepoClient( \Module\OAuth2\Services\Repository\IOC::Clients() )
            ->setRepoUser( \Module\OAuth2\Services\Repository\IOC::Users() )
            ->setRepoRefreshToken( \Module\OAuth2\Services\Repository\IOC::RefreshTokens() )
            ->setRepoAccessToken( ($this->repoAccessToken) ? $this->repoAccessToken: \Module\OAuth2\Services\Repository\IOC::AccessTokens() )
        ;

        return $grantType;
    }


    // ..

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
