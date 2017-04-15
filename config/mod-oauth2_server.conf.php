<?php
use Module\Authorization\Services\ServiceAuthenticatorsContainer;
use Module\Authorization\Services\ServiceGuardsContainer;
use Module\OAuth2;

return [

    \Module\OAuth2\Module::CONF_KEY => [
        OAuth2\Services\ServiceGrantsContainer::CONF => [
            // Capped Container Of Available Grants
            'services' => [
                ## Grant Authorization Code:
                'authorization_code' => OAuth2\Services\Grant\ServiceAuthorizationCode::class,
                ## Grant Authorization Implicit:
                'implicit'           => OAuth2\Services\Grant\ServiceImplicit::class,
                ## Grant Client Credential:
                'client_credentials' => OAuth2\Services\Grant\ServiceClientCredential::class,
                ## Grant Password:
                'password'           => OAuth2\Services\Grant\ServicePassword::class,
                ## Grant Refresh Token:
                'refresh_token'      => OAuth2\Services\Grant\ServiceRefreshToken::class,
                ## Grant Extension Validate Token:
                \Poirot\OAuth2\Server\Grant\GrantExtensionTokenValidation::TYPE_GRANT
                    => OAuth2\Services\Grant\ServiceExtensionValidation::class
            ],
        ],
    ],


    # Authorization:

    \Module\Authorization\Module::CONF_KEY => array(
        ServiceAuthenticatorsContainer::CONF => array(
            'plugins_container' => array(
                'services' => array(
                    // Authenticators Services
                    OAuth2\Services\ServiceAuthenticatorDefault::class,
                ),
            ),
        ),
        ServiceGuardsContainer::CONF => array(
            'plugins_container' => array(
                'services' => array(
                    // Guards Services
                    'oauth_routes' => OAuth2\Services\ServiceAuthGuard::class,
                ),
            ),
        ),
    ),


    # Mongo Driver:

    Module\MongoDriver\Module::CONF_KEY 
    => [
        \Module\MongoDriver\Services\aServiceRepository::CONF_REPOSITORIES => [
            \Module\OAuth2\Services\Repository\ServiceRepoClients::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'oauth.clients',
                    // which client to connect and query with
                    'client' => 'master',
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['identifier' => 1]],
                        ['key' => ['identifier' => 1, 'secret_key' => 1]],
                    ],],],

            \Module\OAuth2\Services\Repository\ServiceRepoUsersApprovedClients::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'oauth.users.approved_clients',
                    // which client to connect and query with
                    'client' => 'master',
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['user' => 1,]],
                        ['key' => ['user' => 1,  'clients_approved.client' => 1]],
                    ],],],

            \Module\OAuth2\Services\Repository\ServiceRepoValidationCodes::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'oauth.users.validation_codes',
                    // which client to connect and query with
                    'client' => 'master',
                    // ensure indexes
                    'indexes' => [
                        [ 'key' => ['validation_code' => 1, ] ],
                        [ 'key' => ['user_identifier' => 1, ] ],
                        [ 'key' => ['user_identifier'=>1, 'auth_codes.type'=>1, 'auth_codes.validated'=>1, ] ],
                        // db.oauth.users.validation_codes.createIndex({"date_mongo_expiration": 1}, {expireAfterSeconds: 0});
                        [ 'key' => ['date_mongo_expiration' => 1 ], 'expireAfterSeconds'=> 0],
                    ],],],

            \Module\OAuth2\Services\Repository\ServiceRepoUsers::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'oauth.users',
                    // which client to connect and query with
                    'client' => 'master',
                    // ensure indexes
                    'indexes' => [
                        [ 'key' => ['date_created_mongo'=>1, ] ],
                        [ 'key' => ['identifiers.type'=>1, 'identifiers.value'=>1, 'identifiers.validated'=>1, ] ],
                    ],],],],
    ],


    # View Renderer:

    \Poirot\Application\Sapi\Server\Http\RenderStrategy\ListenersRenderDefaultStrategy::CONF_KEY
    => [
        \Poirot\Application\Sapi\Server\Http\Service\ServiceViewModelResolver::CONF_KEY => array(
            'Poirot\Loader\LoaderNamespaceStack' => array(
                // Use Default Theme Folder To Achieve Views
                'main/oauth/' => __DIR__. '/../view/main/oauth',
                'error/oauth/' => __DIR__ . '/../view/error/oauth', // Looks for Errors In This Folder
            ),
        ),

        \Poirot\Application\Sapi\Server\Http\RenderStrategy\DefaultStrategy\ListenerError::CONF_KEY => [
            // Display Authentication Exceptions Specific Template
            \Poirot\OAuth2\Server\Exception\exOAuthServer::class => 'error/oauth/oauth-server',
        ],
    ],
];
