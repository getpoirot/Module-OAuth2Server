<?php
return array(

    \Module\OAuth2\Module::CONF_KEY 
    => array(
        \Module\OAuth2\Services\ServiceGrantResponder::CONF_KEY => array(
            ## Options given to GrantResponder Service
            'attached_grants' => array(
                ## Grant Authorization Code:
                array(
                    \Poirot\Config\INIT_INS => array(
                        \Poirot\OAuth2\Server\Grant\GrantAuthCode::class,
                        'options' => array(
                            'retrieve_user_callback' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/actions/RetrieveAuthenticatedUser'), ),
                            'repo_client' => array(
                                // Clients as registered service
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Clients'), ),
                            'repo_user' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Users'),  ),
                            'repo_auth_code' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/AuthorizationCode'),  ),
                            'repo_refresh_token' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/RefreshToken'),  ),
                            'repo_access_token' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/AccessToken'),  ),
                        ),
                    ),
                ),
                ## Grant Authorization Implicit:
                array(
                    \Poirot\Config\INIT_INS => array(
                        \Poirot\OAuth2\Server\Grant\GrantImplicit::class,
                        'options' => array(
                            'retrieve_user_callback' => array(
                                // Clients as registered service
                                \Poirot\Config\INIT_INS => array('/module/oauth2/actions/RetrieveAuthenticatedUser'), ),
                            'repo_client' => array(
                                // Clients as registered service
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Clients'), ),
                            'repo_access_token' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/AccessToken'), ),
                            ),
                    ),
                ),
                ## Grant Client Credential:
                array(
                    \Poirot\Config\INIT_INS => array(
                        \Poirot\OAuth2\Server\Grant\GrantClientCredentials::class,
                        'options' => array(
                            'repo_client' => array(
                                // Clients as registered service
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Clients'), ),
                            'repo_access_token' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/AccessToken'), ),  ),  ),  ),

                ## Grant Password:
                array(
                    \Poirot\Config\INIT_INS => array(
                        \Poirot\OAuth2\Server\Grant\GrantPassword::class,
                        'options' => array(
                            'repo_client' => array(
                                // Clients as registered service
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Clients'),  ),
                            'repo_user' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Users'),  ),
                            'repo_access_token' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/AccessToken'),  ),
                            'repo_refresh_token' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/RefreshToken'),  ),  ),  ),  ),

                ## Grant Refresh Token:
                array(
                    \Poirot\Config\INIT_INS => array(
                        \Poirot\OAuth2\Server\Grant\GrantRefreshToken::class,
                        'options' => array(
                            'repo_client' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Clients'),  ),
                            'repo_user' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Users'),  ),
                            'repo_access_token' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/AccessToken'),  ),
                            'repo_refresh_token' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/RefreshToken'),  ),  ),  ),  ),

                ## Grant Extension Validate Token:
                array(
                    \Poirot\Config\INIT_INS => array(
                        \Poirot\OAuth2\Server\Grant\GrantExtensionTokenValidation::class,
                        'options' => array(
                            'repo_client' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Clients'),  ),
                            'repo_user' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Users'),  ),
                            'repo_access_token' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/AccessToken'),  ),
                            'repo_refresh_token' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/RefreshToken'),  ),  ),  ),  ),

    ),  ),  ),
    
    
    Module\Authorization\Module::CONF_KEY 
    => array(
        \Module\Authorization\Module\AuthenticatorFacade::CONF_KEY_AUTHENTICATORS => array(
            \Module\OAuth2\Module::AUTHENTICATOR => array(
                'realm'      => \Poirot\AuthSystem\Authenticate\Identifier\aIdentifier::DEFAULT_REALM,
                'identifier' => array(
                    \Poirot\Config\INIT_INS   => array(
                        \Poirot\AuthSystem\Authenticate\Identifier\IdentifierWrapIdentityMap::class,
                        'options' => array(
                            'identifier' => array(
                                \Poirot\Config\INIT_INS   => array(
                                    \Poirot\AuthSystem\Authenticate\Identifier\IdentifierHttpBasicAuth::class,
                                    'options' => array(
                                        'credential_adapter' => array(
                                            \Poirot\Config\INIT_INS => array(
                                                \Module\OAuth2\Model\Authenticate\IdentityCredentialDigestRepoUser::class,
                                                'options' => array(
                                                    ## Users as registered service
                                                    'repo_users' => array(
                                                        \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Users')
                                                    ), ),  ),  ),  ),  ),
                            ),
                            'identity' => array(
                                \Poirot\Config\INIT_INS   => array(
                                    \Module\OAuth2\Model\Authenticate\IdentityFulfillmentLazy::class,
                                    'options' => array(
                                        'provider' => array(
                                            \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Users')
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),

                ## default adapter to authenticator::authenticate
                'adapter' => array(
                    \Poirot\Config\INIT_INS => array(
                        \Module\OAuth2\Model\Authenticate\IdentityCredentialDigestRepoUser::class,
                        'options' => array(
                            ## Users as registered service
                            'repo_users' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Users')
                            ), ),  ),  ), ),  ),


        \Module\Authorization\Module\AuthenticatorFacade::CONF_KEY_GUARDS => array(
            'oauth_routes' => array(
                \Poirot\Config\INIT_INS => array(
                    \Module\Authorization\Guard\GuardRoute::class,
                    'options' => array(
                        'authenticator' => \Module\OAuth2\Module::AUTHENTICATOR,
                        'routes_denied' => array(
                            'main/oauth/authorize',  ),  ),  ),  ),  ),
    ),


    // TODO mongo index fields as Entity::FIELD_CONST

    Module\MongoDriver\Module::CONF_KEY 
    => array(
        \Module\MongoDriver\Services\aServiceRepository::CONF_KEY => array(
            \Module\OAuth2\Services\Repository\ServiceRepoClients::class => array(
                'collection' => array(
                    // query on which collection
                    'name' => 'oauth.clients',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => array(
                        array( 'key' => array('identifier' => 1, 'secret_key' => 1) ),  ),  ),  ),

            \Module\OAuth2\Services\Repository\ServiceRepoUsersApprovedClients::class => array(
                'collection' => array(
                    // query on which collection
                    'name' => 'oauth.users.approved_clients',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => array(
                        array( 'key' => array('user_identifier' => 1,  ) ),
                        array( 'key' => array('user_identifier' => 1,  'clients_approved.client_identifier' => 1) ),
                    ),  ),  ),

            \Module\OAuth2\Services\Repository\ServiceRepoUsers::class => array(
                'collection' => array(
                    // query on which collection
                    'name' => 'oauth.users',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => array(
                        array( 'key' => array('identifier' => 1, 'credential' => 1) ),  ),  ),  ),  ),
    ),

    // View Renderer Options
    \Poirot\Application\Sapi\Server\Http\ViewRenderStrategy\ListenersRenderDefaultStrategy::CONF_KEY
    => array(
        \Poirot\Application\Sapi\Server\Http\ViewRenderStrategy\DefaultStrategy\ListenerError::CONF_KEY => array(
            // Display Authentication Exceptions Specific Template
            \Poirot\OAuth2\Server\Exception\exOAuthServer::class => 'error/oauth-server',
        ),
    ),
);
