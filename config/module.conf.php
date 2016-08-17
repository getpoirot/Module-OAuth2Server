<?php
return array(

    Module\Authorization\Module::CONF_KEY => array(
        \Module\Authorization\Module\AuthenticatorFacade::CONF_KEY_GUARDS => array(
            'oauth_routes' => array(
                '_class_' => array(
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
