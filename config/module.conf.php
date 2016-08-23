<?php
return array(

    \Module\OAuth2\Module::CONF_KEY => array(
        \Module\OAuth2\Services\ServiceGrantResponder::CONF_KEY => array(
            ## Options given to GrantResponder Service
            'attached_grants' => array(
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

    ),  ),  ),
    
    
    Module\Authorization\Module::CONF_KEY => array(
        \Module\Authorization\Module\AuthenticatorFacade::CONF_KEY_AUTHENTICATORS => array(
            \Module\OAuth2\Module::AUTHENTICATOR => array(
                'realm'      => \Poirot\AuthSystem\Authenticate\Identifier\aIdentifier::DEFAULT_REALM,
                'identifier' => array(
                    \Poirot\Config\INIT_INS   => array(
                        '\Poirot\AuthSystem\Authenticate\Identifier\IdentifierHttpBasicAuth',
                        'options' => array(
                            'credential_adapter' => array(
                                \Poirot\Config\INIT_INS => array(
                                    'Poirot\AuthSystem\Authenticate\RepoIdentityCredential\IdentityCredentialDigestFile',
                                    'options' => array(
                                        'pwd_file_path' => __DIR__.'/../data/users.pws', ),  ),  ),  ),  ),  ),

                ## default adapter to authenticator::authenticate
                'adapter' => array(
                    \Poirot\Config\INIT_INS   => array(
                        '\Poirot\AuthSystem\Authenticate\RepoIdentityCredential\IdentityCredentialDigestFile',
                        'options' => array(
                            'pwd_file_path' => __DIR__.'/../data/users.pws',  ),  ),  ),  ),  ),

        \Module\Authorization\Module\AuthenticatorFacade::CONF_KEY_GUARDS => array(
            'oauth_routes' => array(
                \Poirot\Config\INIT_INS => array(
                    \Module\Authorization\Guard\GuardRoute::class,
                    'options' => array(
                        'authenticator' => \Module\Authorization\Module\AuthenticatorFacade::AUTHENTICATOR_DEFAULT,
                        'routes_denied' => array(
                            'main/oauth/authorize',  ),  ),  ),  ),  ),
    ),

    
    Module\MongoDriver\Module::CONF_KEY => array(
        \Module\MongoDriver\Services\aServiceRepository::CONF_KEY => array(
            \Module\OAuth2\Services\Repository\ServiceRepoClients::class => array(
                'collection' => array(
                    // query on which collection
                    'name' => 'oauth.clients',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => array(
                        array( 'key' => array('_id' => 1, 'secret_key' => 1) ),  ),  ),  ),

            \Module\OAuth2\Services\Repository\ServiceRepoUsers::class => array(
                'collection' => array(
                    // query on which collection
                    'name' => 'oauth.users',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => array(
                        array( 'key' => array('username' => 1, 'credential' => 1) ),  ),  ),  ),  ),

        'clients' => array( 'anar_production'  => array(
                ## mongodb://[username:password@]host1[:port1][,host2[:port2],...[,hostN[:portN]]][/[database][?options]]
                #- anything that is a special URL character needs to be URL encoded.
                ## This is particularly something to take into account for the password,
                #- as that is likely to have characters such as % in it.
                'host' => 'mongodb://91.98.28.230:27017',

                ## Required Database Name To Client Connect To
                'db'   => 'kookoja',  ),  ),

    ),
);
