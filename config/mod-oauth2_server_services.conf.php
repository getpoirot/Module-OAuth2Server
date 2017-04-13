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
    'services' => [
        BuildOAuthModuleServices::SERVICE_AUTHENTICATOR
            => \Module\OAuth2\Services\ServiceAuthenticatorDefault::class
    ],
    'nested' => [
        'repository' => [
            // Define Default Services
            'services' =>
                [
                    BuildOAuthModuleServices::SERVICE_CLIENTS
                       => \Module\OAuth2\Services\Repository\ServiceRepoClients::class,

                    BuildOAuthModuleServices::SERVICE_USERS
                       => \Module\OAuth2\Services\Repository\ServiceRepoUsers::class,

                    BuildOAuthModuleServices::SERVICE_ACCESS_TOKENS => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\AccessTokens::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],

                    BuildOAuthModuleServices::SERVICE_REFRESH_TOKENS => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\RefreshTokens::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],

                    BuildOAuthModuleServices::SERVICE_AUTH_CODES => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\AuthorizationCodes::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],


                    BuildOAuthModuleServices::SERVICE_USERS_APPROVED_CLIENTS
                    => \Module\OAuth2\Services\Repository\ServiceRepoUsersApprovedClients::class,

                    BuildOAuthModuleServices::SERVICE_VALIDATION_CODES
                    => \Module\OAuth2\Services\Repository\ServiceRepoValidationCodes::class,
                ],
        ],
    ],
];
