<?php
namespace Module\OAuth2\Services;

use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Interfaces\Server\Repository\iRepoUsersApprovedClients;

use Poirot\OAuth2\Interfaces\Server\Repository;
use Poirot\AuthSystem\Authenticate\Interfaces\iAuthenticator;
use Poirot\Ioc\Container\BuildContainer;

use Poirot\OAuth2\Server\Grant\GrantAggregateGrants;


class BuildServices
    extends BuildContainer
{
    const AUTHENTICATOR          = 'Authenticator';

    const CLIENTS                = 'Clients';
    const USERS                  = 'Users';
    const ACCESS_TOKENS          = 'AccessTokens';
    const REFRESH_TOKENS         = 'RefreshTokens';
    const AUTH_CODES             = 'AuthCodes';
    const USERS_APPROVED_CLIENTS = 'Users.ApprovedClients';
    const VALIDATION_CODES       = 'ValidationCodes';


    protected $implementations =
        [
            // Grant Responders while response to token requests
            'GrantResponder' => GrantAggregateGrants::class,

            // Default Authenticator when we want login and identify user in console...
            'Authenticator'  => iAuthenticator::class
        ];


    protected $services =
        [
            // Default Grant Responder
            'GrantResponder' => \Module\OAuth2\Services\ServiceGrantResponder::class,
        ];

    protected $nested =
        [
            'repository' => [
                'implementations' => [
                    // Services must implement these interfaces
                    self::CLIENTS                => Repository\iRepoClients::class,
                    self::USERS                  => iRepoUsers::class,
                    self::ACCESS_TOKENS          => Repository\iRepoAccessTokens::class,
                    self::REFRESH_TOKENS         => Repository\iRepoRefreshTokens::class,
                    self::AUTH_CODES             => Repository\iRepoAuthCodes::class,
                    self::USERS_APPROVED_CLIENTS => iRepoUsersApprovedClients::class,
                    self::VALIDATION_CODES       => iRepoValidationCodes::class,
                ],
            ],
        ];
}
