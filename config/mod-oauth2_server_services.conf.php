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
use Module\OAuth2\Services\BuildServices;

return [
    'services' => [
        BuildServices::AUTHENTICATOR
            => \Module\OAuth2\Services\ServiceAuthenticatorDefault::class
    ],
    'nested' => [
        'repository' => [
            // Define Default Services
            'services' =>
                [
                    BuildServices::CLIENTS
                       => \Module\OAuth2\Services\Repository\ServiceRepoClients::class,

                    BuildServices::USERS
                       => \Module\OAuth2\Services\Repository\ServiceRepoUsers::class,

                    BuildServices::ACCESS_TOKENS => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\AccessTokens::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],

                    BuildServices::REFRESH_TOKENS => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\RefreshTokens::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],

                    BuildServices::AUTH_CODES => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\AuthorizationCodes::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],


                    BuildServices::USERS_APPROVED_CLIENTS
                    => \Module\OAuth2\Services\Repository\ServiceRepoUsersApprovedClients::class,

                    BuildServices::VALIDATION_CODES
                    => \Module\OAuth2\Services\Repository\ServiceRepoValidationCodes::class,
                ],
        ],
    ],
];
