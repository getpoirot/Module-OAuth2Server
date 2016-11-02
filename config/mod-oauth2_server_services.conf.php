<?php
/**
 * Default OAuth2 IOC Services
 *
 * @see \Poirot\Ioc\Container\BuildContainer
 *
 * ! These Services Can Be Override By Name (also from other modules).
 *   Nested in IOC here at: /module/oauth2/services
 *
 *
 * @see \Module\OAuth2::getServices()
 */
use Module\OAuth2\Services\BuildOAuthModuleServices;

return [
    'nested' => [
        'repository' => [
            // Define Default Services
            'services' =>
                [
                    BuildOAuthModuleServices::SERVICE_NAME_CLIENTS
                       => \Module\OAuth2\Services\Repository\ServiceRepoClients::class,

                    BuildOAuthModuleServices::SERVICE_NAME_USERS
                       => \Module\OAuth2\Services\Repository\ServiceRepoUsers::class,

                    BuildOAuthModuleServices::SERVICE_NAME_USERS_APPROVED_CLIENTS
                       => \Module\OAuth2\Services\Repository\ServiceRepoUsersApprovedClients::class,

                    BuildOAuthModuleServices::SERVICE_NAME_ACCESS_TOKENS => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\AccessTokens::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],

                    BuildOAuthModuleServices::SERVICE_NAME_REFRESH_TOKENS => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\RefreshTokens::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],

                    BuildOAuthModuleServices::SERVICE_NAME_AUTH_CODES => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\AuthorizationCodes::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],
                ],
        ],
    ],
];
