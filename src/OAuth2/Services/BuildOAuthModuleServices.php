<?php
namespace Module\OAuth2\Services;

use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Interfaces\Server\Repository\iRepoUsersApprovedClients;

use Poirot\AuthSystem\Authenticate\Interfaces\iAuthenticator;
use Poirot\Ioc\Container\BuildContainer;

use Poirot\OAuth2\Server\Grant\GrantAggregateGrants;


class BuildOAuthModuleServices
    extends BuildContainer
{
    const SERVICE_NAME_AUTHENTICATOR          = 'Authenticator';

    const SERVICE_NAME_CLIENTS                = 'Clients';
    const SERVICE_NAME_USERS                  = 'Users';
    const SERVICE_NAME_ACCESS_TOKENS          = 'AccessTokens';
    const SERVICE_NAME_REFRESH_TOKENS         = 'RefreshTokens';
    const SERVICE_NAME_AUTH_CODES             = 'AuthCodes';

    const SERVICE_NAME_USERS_APPROVED_CLIENTS = 'Users.ApprovedClients';
    const SERVICE_NAME_VALIDATION_CODES       = 'ValidationCodes';


    protected $implementations
        = [
            'GrantResponder' => GrantAggregateGrants::class,
            'Authenticator'  => iAuthenticator::class
        ];

    protected $services
        = [
            'GrantResponder' => \Module\OAuth2\Services\ServiceGrantResponder::class,
        ];

    protected $nested
        = [
            'repository' => [
                'implementations' => [
                    // Services must implement these interfaces
                    self::SERVICE_NAME_CLIENTS
                      => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoClients::class,

                    self::SERVICE_NAME_USERS
                      => iRepoUsers::class,

                    self::SERVICE_NAME_ACCESS_TOKENS
                      => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessTokens::class,

                    self::SERVICE_NAME_REFRESH_TOKENS
                      => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoRefreshTokens::class,

                    self::SERVICE_NAME_AUTH_CODES
                      => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoAuthCodes::class,


                    self::SERVICE_NAME_USERS_APPROVED_CLIENTS
                    => iRepoUsersApprovedClients::class,

                    self::SERVICE_NAME_VALIDATION_CODES
                    => iRepoValidationCodes::class,
                ],
            ],
        ];
}
