<?php
use Module\OAuth2\Services\BuildOAuthModuleServices;

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
                            'repo_client' => [
                                // Clients as registered service
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_CLIENTS],],
                            'repo_user' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_USERS],],
                            'repo_auth_code' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_AUTH_CODES],],
                            'repo_refresh_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_REFRESH_TOKENS],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_ACCESS_TOKENS],],
                        ],
                    ],
                ],
                ## Grant Authorization Implicit:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantImplicit::class,
                        'options' => [
                            'retrieve_user_callback' => [
                                // Clients as registered service
                                \Poirot\Ioc\INST => [\Module\OAuth2\Actions\Users\RetrieveAuthenticatedUser::class],],
                            'repo_client' => [
                                // Clients as registered service
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_CLIENTS],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_ACCESS_TOKENS],],
                        ],
                    ],
                ],
                ## Grant Client Credential:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantClientCredentials::class,
                        'options' => [
                            'repo_client' => [
                                // Clients as registered service
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_CLIENTS],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_ACCESS_TOKENS],],],],],

                ## Grant Password:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantPassword::class,
                        'options' => [
                            'repo_client' => [
                                // Clients as registered service
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_CLIENTS],],
                            'repo_user' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_USERS],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_ACCESS_TOKENS],],
                            'repo_refresh_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_REFRESH_TOKENS],],],],],

                ## Grant Refresh Token:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantRefreshToken::class,
                        'options' => [
                            'repo_client' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_CLIENTS],],
                            'repo_user' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_USERS],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_ACCESS_TOKENS],],
                            'repo_refresh_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_REFRESH_TOKENS],],],],],

                ## Grant Extension Validate Token:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantExtensionTokenValidation::class,
                        'options' => [
                            'repo_client' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_CLIENTS],],
                            'repo_user' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_USERS],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_ACCESS_TOKENS],],
                            'repo_refresh_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/'.BuildOAuthModuleServices::SERVICE_NAME_REFRESH_TOKENS],],],],],

            ],],],
    
    
    Module\Authorization\Module::CONF_KEY 
    => [
        \Module\Authorization\Module\AuthenticatorFacade::CONF_KEY_AUTHENTICATORS => [
            \Module\OAuth2\Module::AUTHENTICATOR => [
                \Poirot\Ioc\INST => ['/module/oauth2/services/'.BuildOAuthModuleServices::SERVICE_NAME_AUTHENTICATOR],
                ],],


        \Module\Authorization\Module\AuthenticatorFacade::CONF_KEY_GUARDS => [
            'oauth_routes' => [
                \Poirot\Ioc\INST => [
                    \Module\Authorization\Guard\GuardRoute::class,
                    'options' => [
                        'authenticator' => \Module\OAuth2\Module::AUTHENTICATOR,
                        'routes_denied' => [
                            'main/oauth/authorize',
                            'main/oauth/me',
                        ],],],],],
    ],


    // TODO mongo index fields as Entity::FIELD_CONST

    Module\MongoDriver\Module::CONF_KEY 
    => [
        \Module\MongoDriver\Services\aServiceRepository::CONF_KEY => [
            \Module\OAuth2\Services\Repository\ServiceRepoClients::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'oauth.clients',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['identifier' => 1, 'secret_key' => 1]],],],],

            \Module\OAuth2\Services\Repository\ServiceRepoUsersApprovedClients::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'oauth.users.approved_clients',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['user_identifier' => 1,]],
                        ['key' => ['user_identifier' => 1,  'clients_approved.client_identifier' => 1]],
                    ],],],

            \Module\OAuth2\Services\Repository\ServiceRepoValidationCodes::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'oauth.users.validation_codes',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => [
                    ],],],

            \Module\OAuth2\Services\Repository\ServiceRepoUsers::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'oauth.users',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => [
                        [ 'key' => ['date_created_mongo'=>1, ] ],
                        [ 'key' => ['identifiers.type'=>1, 'identifiers.value'=>1, 'identifiers.validated'=>1, ] ],
                    ],],],],
    ],

    // View Renderer Options
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
