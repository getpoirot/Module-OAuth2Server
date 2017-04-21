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
use Module\OAuth2\Services;
use Poirot\Ioc\Container\BuildContainer;

return [
    'services' => [
        // Default Authenticator Used By Authorize Module as Authenticators Registered Service
        BuildServices::AUTHENTICATOR => Services\ServiceAuthenticatorDefault::class,

        // Authorize Token By OAuthClient Token Assertion
        'AuthorizeToken' => Services\ServiceAuthorizeToken::class,

        // Available Grants Types Used By Grant Responder
        // defined in BuildServices by default
        #'ContainerGrants' => Services\ServiceGrantsContainer::class,
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
                    BuildContainer::INST => \Poirot\OAuth2\Model\Repo\Stateless\AccessTokenRepo::class,
                    // options:
                    'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                ],
                BuildServices::REFRESH_TOKENS         => [
                    BuildContainer::INST => \Poirot\OAuth2\Model\Repo\Stateless\RefreshTokens::class,
                    // options:
                    'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                ],
                BuildServices::AUTH_CODES             => [
                    BuildContainer::INST => \Poirot\OAuth2\Model\Repo\Stateless\AuthorizationCodes::class,
                    // options:
                    'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                ],
            ],
        ],
    ],
];
