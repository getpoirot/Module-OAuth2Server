<?php
use Module\OAuth2;
use Module\Authorization\Services\ServiceAuthenticatorsContainer;
use Module\Authorization\Services\ServiceGuardsContainer;

use Module\HttpRenderer\Services\RenderStrategy\ListenersRenderDefaultStrategy;
use Poirot\OAuth2\Server\Grant\GrantExtensionTokenValidation;


return [

    \Module\OAuth2\Module::CONF_KEY => [

        OAuth2\Services\ServiceGrantsContainer::CONF => [
            // Capped Container Of Available Grants
            'grants' => [
                ## Grant Extension Validate Token:
                GrantExtensionTokenValidation::TYPE_GRANT => OAuth2\Services\Grant\ServiceExtensionValidation::class
            ],
        ],

        # extra config
        // Server Automatically Choose a Username For Registered User If Not Sent
        'allow_server_pick_username' => true,

        'mediums' => [
            'mobile' => [
                // Path to Template file or String
                'message_verification' => 'کد فعال سازی شما %s',
            ],
            'email' => [
                // Path to Template file or String
                'message_verification' => '',
            ],
        ],
    ],


    # Path

    \Module\Foundation\Services\PathService::CONF => [
        'paths' => [
            // According to route name 'www-assets' to serve statics files
            // @see cor-http_foundation.routes
            'www-alter' => "\$baseUrl/auth/www/",
        ],
    ],


    # Authorization:

    \Module\Authorization\Module::CONF_KEY => [
        ServiceAuthenticatorsContainer::CONF => [
            'plugins_container' => [
                'services' => [
                    // Authenticators Services
                    'module.oauth2.default_authenticator' => OAuth2\Services\ServiceAuthenticatorDefault::class,
                ],
            ],
        ],
        ServiceGuardsContainer::CONF => [
            'plugins_container' => [
                'services' => [
                    // Guards Services
                    'oauth_routes' => OAuth2\Services\ServiceAuthGuard::class,
                ],
            ],
        ],
    ],


    # Mongo Driver:

    Module\MongoDriver\Module::CONF_KEY => [

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

    // View Renderer Options
    ListenersRenderDefaultStrategy::CONF_KEY => [
        'themes' => [
            'oauth2server' => [
                'dir' => __DIR__.'/../theme_alter',
                // (bool) func()
                // function will instantiated for resolve arguments
                // or true|false
                'when' => function($routerMatch) {
                    // Active Template When We Are On OAuth Route
                    return ( $routerMatch && strpos($routerMatch->getName(), 'main/oauth/') === 0 );
                }, // always use this template
                'priority' => 100,
                'layout' => [
                    'default' => 'default',
                    'exception' => [
                        'Exception' => ['error/error', 'blank'],
                        'Poirot\Application\Exception\exRouteNotMatch' => 'error/404',
                        // Display Authentication Exceptions Specific Template
                        \Poirot\OAuth2\Server\Exception\exOAuthServer::class => 'error/oauth/oauth-server',
                    ],
                ],
            ],
        ],
    ],
];
