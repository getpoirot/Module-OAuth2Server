<?php
/** @see \Poirot\Ioc\Container\BuildContainer */
return [
    'nested' => [
        'repository' => [
            'services' => [
                \Module\OAuth2\Services\Repository\ServiceRepoClients::class,
                \Module\OAuth2\Services\Repository\ServiceRepoUsers::class,
                'AccessToken' => [
                    ':class'  => \Module\OAuth2\Model\Repo\Stateless\AccessTokens::class,
                    'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                ],
                'RefreshToken' => [
                    ':class'  => \Module\OAuth2\Model\Repo\Stateless\RefreshTokens::class,
                    'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                ],
                'AuthorizationCode' => [
                    ':class'  => \Module\OAuth2\Model\Repo\Stateless\AuthorizationCodes::class,
                    'encryption' => new \Poirot\OAuth2\Crypt\Base64\Crypt(),
                ],
            ],
        ],
    ],
];
