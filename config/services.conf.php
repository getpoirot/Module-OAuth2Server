<?php
use Module\OAuth2;
use Module\OAuth2\Interfaces\Repository as OAuth2ModuleRepository;
use Module\OAuth2\Services;
use \Poirot\OAuth2\Interfaces\Server\Repository;

return [
    'implementations' => [
        Services::EventsHeap => \Poirot\Events\Interfaces\iEventHeap::class,
        Services::GrantPlugins => Services\GrantPlugins::class,
    ],
    'services' => [
        Services::EventsHeap => OAuth2\Events\OAuthEventHeap::class,
        Services::GrantPlugins => Services\GrantPluginsService::class,
    ],
    'nested' => [
        'repositories' => [
            'implementations' => [
                Services\Repositories::AccessTokens => Repository\iRepoAccessTokens::class,
                Services\Repositories::RefreshTokens => Repository\iRepoRefreshTokens::class,
                Services\Repositories::AuthCodes => Repository\iRepoAuthCodes::class,
                Services\Repositories::Clients => Repository\iRepoClients::class,
                Services\Repositories::Users => Repository\iRepoUsers::class,
                Services\Repositories::UsersApprovedClients => OAuth2ModuleRepository\iRepoUsersApprovedClients::class,
                Services\Repositories::ValidationCodes => OAuth2ModuleRepository\iRepoValidationCodes::class,
            ],
            'services' => \Poirot\Std\catchIt(function () {
                if (false === $c = \Poirot\Config\load(__DIR__ . '/mod-oauth2/repositories'))
                    throw new \Exception('Config (oauth2server/repositories) not loaded.');

                return $c->value;
            }),
        ],
    ],
];
