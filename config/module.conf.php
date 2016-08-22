<?php
return array(

    \Module\OAuth2\Module::CONF_KEY => array(
        \Module\OAuth2\Services\ServiceGrantResponder::CONF_KEY => array(
            ## Options given to GrantResponder Service
            'attached_grants' => array(
                array(
                    \Poirot\Config\INIT_INS => array(
                        \Poirot\OAuth2\Server\Grant\GrantClientCredentials::class,
                        'options' => array(
                            'repo_client' => array(
                                \Poirot\Config\INIT_INS => array('/module/oauth2/services/repository/Clients'), // this is registered service
                            ),
                            'repo_access_token' => new \Module\OAuth2\Model\Repo\Stateless\AccessTokens(
                                new \Poirot\OAuth2\Crypt\Base64\Crypt()
                            ),
                            // TODO with this config php exit on instancing initialized object without no error/exception
                            //      on Container ServiceInstance->newService()
                            /*'repo_access_token' => array(
                                \Poirot\Config\INIT_INS => array(
                                    \Module\OAuth2\Model\Repo\Stateless\AccessTokens::class,
                                    'encryption' => array(
                                        \Poirot\Config\INIT_INS => array(
                                            \Poirot\OAuth2\Crypt\Base64\Crypt::class,
                                        ),
                                    ),
                                ),
                            ),*/
                        ),
                    ),
                ),
            ),
        ),
    ),
    
    
    Module\Authorization\Module::CONF_KEY => array(
        \Module\Authorization\Module\AuthenticatorFacade::CONF_KEY_GUARDS => array(
            'oauth_routes' => array(
                \Poirot\Config\INIT_INS => array(
                    \Module\Authorization\Guard\GuardRoute::class,
                    'options' => array(
                        'authenticator' => \Module\Authorization\Module\AuthenticatorFacade::AUTHENTICATOR_DEFAULT,
                        'routes_denied' => array(
                            'main/oauth/authorize',
                        ),
                    ),
                )
            ),
        ),
    ),

    
    Module\MongoDriver\Module::CONF_KEY => array(

        \Module\MongoDriver\Services\aServiceRepository::CONF_KEY => array(
            // Configuration of Repository Service.
            // Usually Implemented with modules that implement mongo usage
            // with specific key name as repo name.
            // @see aServiceRepository bellow
            \Module\OAuth2\Services\Repository\ServiceRepoClients::class => array(
                'collection' => array(
                    // query on which collection
                    'name' => 'oauth.clients',
                    // which client to connect and query with
                    'client' => \Module\MongoDriver\Module\MongoDriverManagementFacade::CLIENT_DEFAULT,
                    // ensure indexes
                    'indexes' => array(
                        array( 'key' => array('_id' => 1, 'secret_key' => 1) ),
                    )
                ),
            ),
        ),

        'clients' => array(
            'anar_production'
            => array(
                ## mongodb://[username:password@]host1[:port1][,host2[:port2],...[,hostN[:portN]]][/[database][?options]]
                #- anything that is a special URL character needs to be URL encoded.
                ## This is particularly something to take into account for the password,
                #- as that is likely to have characters such as % in it.
                'host' => 'mongodb://91.98.28.230:27017',

                ## Required Database Name To Client Connect To
                'db'   => 'kookoja',
            ),
        ),

    ),
);
