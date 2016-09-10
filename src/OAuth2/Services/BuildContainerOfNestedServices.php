<?php
namespace Module\OAuth2\Services;

use Poirot\Ioc\Container\BuildContainer;

use Poirot\OAuth2\Server\Grant\GrantAggregateGrants;

class BuildContainerOfNestedServices
    extends BuildContainer
{
    protected $implementations
        = array(
            'GrantResponder' => GrantAggregateGrants::class,
        );

    protected $services
        = array(
            'GrantResponder' => \Module\OAuth2\Services\ServiceGrantResponder::class,
        );

    protected $nested
        = array(
            'repository' => array(
                'implementations' => array(
                    'clients'           => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoClient::class,
                    'users'             => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoUser::class,
                    'AccessToken'       => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessToken::class,
                    'RefreshToken'      => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoRefreshToken::class,
                    'AuthorizationCode' => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoAuthCode::class,
                ),
            ),
        );
}
