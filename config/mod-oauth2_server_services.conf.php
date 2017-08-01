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
use Module\OAuth2;
use Module\OAuth2\Services\BuildServices;
use Module\OAuth2\Services;
use Poirot\Ioc\instance;


return [
    'services' => [
        // Available Grants Types Used By Grant Responder
        // defined in BuildServices by default
        'ContainerGrants' => new instance(
            Services\ServiceGrantsContainer::class,
            [
                'default_grants' => [
                    ## Grant Authorization Code:
                    'authorization_code' => new instance(
                        OAuth2\Services\Grant\ServiceAuthorizationCode::class
                        , \Poirot\Std\catchIt(function () {
                            if (false === $c = \Poirot\Config\load(__DIR__.'/oauth2server/grant-auth_code'))
                                throw new \Exception('Config (oauth2server/grant-auth_code) not loaded.');

                            return $c->value;
                        })
                    ),
                    ## Grant Authorization Implicit:
                    'implicit'           => OAuth2\Services\Grant\ServiceImplicit::class,
                    ## Grant Client Credential:
                    'client_credentials' => OAuth2\Services\Grant\ServiceClientCredential::class,
                    ## Grant Password:
                    'password'           => new instance(
                        OAuth2\Services\Grant\ServicePassword::class
                        , \Poirot\Std\catchIt(function () {
                            if (false === $c = \Poirot\Config\load(__DIR__.'/oauth2server/grant-password'))
                                throw new \Exception('Config (oauth2server/grant-auth_code) not loaded.');

                            return $c->value;
                        })
                    ),
                    ## Grant Refresh Token:
                    'refresh_token'      => new instance(
                        OAuth2\Services\Grant\ServiceRefreshToken::class
                        , \Poirot\Std\catchIt(function () {
                            if (false === $c = \Poirot\Config\load(__DIR__.'/oauth2server/grant-refresh_token'))
                                throw new \Exception('Config (oauth2server/grant-auth_code) not loaded.');

                            return $c->value;
                        })
                    )
                ]
            ]
        ),

        // Default Authenticator Used By Authorize Module as Authenticators Registered Service
        BuildServices::AUTHENTICATOR => Services\ServiceAuthenticatorDefault::class,

        // Authorize Token By OAuthClient Token Assertion
        'AssertToken' => Services\ServiceAssertToken::class,
    ],
    'nested' => [
        'repository' => [
            // Define Default Services
            'services' => [
                BuildServices::CLIENTS                => Services\Repository\ServiceRepoClients::class,
                BuildServices::USERS                  => Services\Repository\ServiceRepoUsers::class,
                BuildServices::USERS_APPROVED_CLIENTS => Services\Repository\ServiceRepoUsersApprovedClients::class,
                BuildServices::VALIDATION_CODES       => Services\Repository\ServiceRepoValidationCodes::class,
                BuildServices::ACCESS_TOKENS          => [
                    \Poirot\OAuth2\Model\Repo\Stateless\AccessTokenRepo::class,
                    // options:
                    'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                ],
                BuildServices::REFRESH_TOKENS         => [
                    \Poirot\OAuth2\Model\Repo\Stateless\RefreshTokens::class,
                    // options:
                    'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                ],
                BuildServices::AUTH_CODES             => [
                    \Poirot\OAuth2\Model\Repo\Stateless\AuthorizationCodes::class,
                    // options:
                    'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                ],
            ],
        ],
    ],
];
