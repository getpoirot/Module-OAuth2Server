<?php
/*
 * This Configuration file can be extend and override by making a copy into application level config directory
 */
use Module\OAuth2\Services;

return [
    Services\Repositories::AccessTokens => Services\Repositories\RepoAccessTokensService::class,
    Services\Repositories::RefreshTokens => Services\Repositories\RepoAccessTokensService::class,
    Services\Repositories::AuthCodes => Services\Repositories\RepoAccessTokensService::class,
    Services\Repositories::Clients => Services\Repositories\RepoClientsService::class,
    Services\Repositories::Users => Services\Repositories\RepoUsersService::class,
    Services\Repositories::UsersApprovedClients => Services\Repositories\RepoUsersApprovedClientsService::class,
    Services\Repositories::ValidationCodes => Services\Repositories\RepoValidationCodesService::class,
];
