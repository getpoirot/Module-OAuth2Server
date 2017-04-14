<?php
use Module\OAuth2;
use Module\OAuth2\Services\BuildServices;

return [
    \Module\OAuth2\Module::CONF_KEY 
    => [
        \Module\OAuth2\Services\ServiceGrantResponder::CONF_KEY => [
            ## Options given to GrantResponder Service
            'attached_grants' => [
                ## Grant Authorization Code:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantAuthCode::class,
                        'options' => [
                            'retrieve_user_callback' => [
                                \Poirot\Ioc\INST => [\Module\OAuth2\Actions\Users\RetrieveAuthenticatedUser::class],],
                            'repo_auth_code' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildServices::AUTH_CODES],],
                        ], ], ],

                ## Grant Authorization Implicit:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantImplicit::class,
                        'options' => [
                            'retrieve_user_callback' => [
                                // Clients as registered service
                                \Poirot\Ioc\INST => [\Module\OAuth2\Actions\Users\RetrieveAuthenticatedUser::class],],],],],

                ## Grant Client Credential:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantClientCredentials::class,],],

                ## Grant Password:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantPassword::class, ],],

                ## Grant Refresh Token:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantRefreshToken::class, ],],

                ## Grant Extension Validate Token:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantExtensionTokenValidation::class, ],],

            ],
            'options_override' => [
                'default' => [
                    ## Options used by all grant types
                    'repo_client' => [
                        // Clients as registered service
                        \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildServices::CLIENTS],],
                    'repo_access_token' => [
                        \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildServices::ACCESS_TOKENS],],

                    ## Options used by [extension, refresh_token, password, authorization_code]
                    'repo_user' => [
                        \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildServices::USERS],],
                    'repo_refresh_token' => [
                        \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildServices::REFRESH_TOKENS],],


                    ## TTL
                    'ttl_auth_code'     => new \DateInterval('PT5M'),
                    'ttl_refresh_token' => new \DateInterval('P1M'),
                    'ttl_access_token'  => new \DateInterval('PT1H'),
                ],

                // or
                //- 'classGrantName' => $options
            ],
        ],],


    # Authorization:

    \Module\Authorization\Module::CONF_KEY => array(
        'authenticators' => array(
            'services' => array(
                // Authenticators Services
                OAuth2\Services\ServiceAuthenticatorDefault::class,
            ),
        ),
        'guards' => array(
            'services' => array(
                // Guards Services
                'oauth_routes' => OAuth2\Services\ServiceAuthGuard::class,
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
