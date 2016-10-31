<?php
/**
 * @see \Poirot\Ioc\Container\BuildContainer
 *
 * ! These Services Can Be Override By Name (also from other modules).
 *   Nested in IOC here at: /module/oauth2/services
 */
return [
    'nested' => [
        'repository' => [

            // Services must implement these interfaces
            'implementations' => [
                'Clients'           => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoClient::class,
                'Users'             => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoUser::class,
                'AccessToken'       => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessToken::class,
                'RefreshToken'      => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoRefreshToken::class,
                'AuthorizationCode' => \Poirot\OAuth2\Interfaces\Server\Repository\iRepoAuthCode::class,
            ],

            // Define Default Services
            'services' =>
                [
                    // Clients
                    \Module\OAuth2\Services\Repository\ServiceRepoClients::class,

                    // Users
                    \Module\OAuth2\Services\Repository\ServiceRepoUsers::class,

                    // Users.ApprovedClients
                    \Module\OAuth2\Services\Repository\ServiceRepoUsersApprovedClients::class,

                    // AccessToken
                    'AccessToken' => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\AccessTokens::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],

                    // RefreshToken
                    'RefreshToken' => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\RefreshTokens::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],

                    // AuthorizationCode
                    'AuthorizationCode' => [
                        \Poirot\Ioc\Container\BuildContainer::INST
                           => \Poirot\OAuth2\Model\Repo\Stateless\AuthorizationCodes::class,
                        // options:
                        'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                    ],
                ],
        ],
    ],
];
