<?php
return array(

    \Module\OAuth2\Module::CONF_KEY 
    => array(
        \Module\OAuth2\Services\ServiceGrantResponder::CONF_KEY => [
            ## Options given to GrantResponder Service
            'attached_grants' => [
                ## Grant Authorization Code:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantAuthCode::class,
                        'options' => [
                            'retrieve_user_callback' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/actions/RetrieveAuthenticatedUser'],],
                            'repo_client' => [
                                // Clients as registered service
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Clients'],],
                            'repo_user' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Users'],],
                            'repo_auth_code' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/AuthorizationCode'],],
                            'repo_refresh_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/RefreshToken'],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/AccessToken'],],
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
                                \Poirot\Ioc\INST => ['/module/oauth2/actions/RetrieveAuthenticatedUser'],],
                            'repo_client' => [
                                // Clients as registered service
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Clients'],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/AccessToken'],],
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
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Clients'],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/AccessToken'],],],],],

                ## Grant Password:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantPassword::class,
                        'options' => [
                            'repo_client' => [
                                // Clients as registered service
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Clients'],],
                            'repo_user' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Users'],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/AccessToken'],],
                            'repo_refresh_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/RefreshToken'],],],],],

                ## Grant Refresh Token:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantRefreshToken::class,
                        'options' => [
                            'repo_client' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Clients'],],
                            'repo_user' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Users'],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/AccessToken'],],
                            'repo_refresh_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/RefreshToken'],],],],],

                ## Grant Extension Validate Token:
                [
                    \Poirot\Ioc\INST => [
                        \Poirot\OAuth2\Server\Grant\GrantExtensionTokenValidation::class,
                        'options' => [
                            'repo_client' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Clients'],],
                            'repo_user' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Users'],],
                            'repo_access_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/AccessToken'],],
                            'repo_refresh_token' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/RefreshToken'],],],],],

            ],],  ),
    
    
    Module\Authorization\Module::CONF_KEY 
    => [
        \Module\Authorization\Module\AuthenticatorFacade::CONF_KEY_AUTHENTICATORS => [
            \Module\OAuth2\Module::AUTHENTICATOR => [
                'realm'      => \Poirot\AuthSystem\Authenticate\Identifier\aIdentifier::DEFAULT_REALM,
                'identifier' => [
                    \Poirot\Ioc\INST   => [
                        \Poirot\AuthSystem\Authenticate\Identifier\IdentifierWrapIdentityMap::class,
                        'options' => [
                            'identifier' => [
                                \Poirot\Ioc\INST   => [
                                    \Poirot\AuthSystem\Authenticate\Identifier\IdentifierHttpBasicAuth::class,
                                    'options' => [
                                        'credential_adapter' => [
                                            \Poirot\Ioc\INST => [
                                                \Module\OAuth2\Model\Authenticate\IdentityCredentialDigestRepoUser::class,
                                                'options' => [
                                                    ## Users as registered service
                                                    'repo_users' => [
                                                        \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Users']
                                                    ],],],],],],
                            ],
                            'identity' => [
                                \Poirot\Ioc\INST   => [
                                    \Module\OAuth2\Model\Authenticate\IdentityFulfillmentLazy::class,
                                    'options' => [
                                        'provider' => [
                                            \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Users']
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],

                ## default adapter to authenticator::authenticate
                'adapter' => [
                    \Poirot\Ioc\INST => [
                        \Module\OAuth2\Model\Authenticate\IdentityCredentialDigestRepoUser::class,
                        'options' => [
                            ## Users as registered service
                            'repo_users' => [
                                \Poirot\Ioc\INST => ['/module/oauth2/services/repository/Users']
                            ],],],],],],


        \Module\Authorization\Module\AuthenticatorFacade::CONF_KEY_GUARDS => [
            'oauth_routes' => [
                \Poirot\Ioc\INST => [
                    \Module\Authorization\Guard\GuardRoute::class,
                    'options' => [
                        'authenticator' => \Module\OAuth2\Module::AUTHENTICATOR,
                        'routes_denied' => [
                            'main/oauth/authorize',],],],],],
    ],


    // TODO mongo index fields as Entity::FIELD_CONST

    Module\MongoDriver\Module::CONF_KEY 
    => array(
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

            \Module\OAuth2\Services\Repository\ServiceRepoUsers::class => [
                'collection' => [
                    // query on which collection
                    'name' => 'oauth.users',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => [
                        ['key' => ['identifier' => 1, 'credential' => 1]],],],],],
    ),

    // View Renderer Options
    \Poirot\Application\Sapi\Server\Http\ViewRenderStrategy\ListenersRenderDefaultStrategy::CONF_KEY
    => [
        \Poirot\Application\Sapi\Server\Http\ViewRenderStrategy\DefaultStrategy\ListenerError::CONF_KEY => [
            // Display Authentication Exceptions Specific Template
            \Poirot\OAuth2\Server\Exception\exOAuthServer::class => 'error/oauth-server',
        ],
    ],
);
