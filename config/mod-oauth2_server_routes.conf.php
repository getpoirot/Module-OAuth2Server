<?php

use Module\HttpFoundation\Events\Listener\ListenerDispatch;

return [
    /// Override Home Page Route
    //- Redirect to login page
    'home'  => array(
        'route'    => 'RouteSegment',
        'options' => array(
            'criteria'    => '/',
            'match_whole' => true,
        ),
        'params'  => array(
            ListenerDispatch::ACTIONS => function() {
                return new \Module\HttpFoundation\Response\ResponseRedirect(
                    \Module\HttpFoundation\Actions::url('main/oauth/login')
                );
            },
        ),
    ),

    'oauth'  => [
        'routes' => [

            ## OAuth2 Specific EndPoints -----------------------------------------------------------------\
            'authorize' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/auth',
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [ '/module/oauth2/actions/AuthorizePage' ],
                ],
            ],
            'token' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/auth/token',
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [ '/module/oauth2/actions/RespondToTokenRequest' ],
                ],
            ],

            ## Register User / Login ---------------------------------------------------------------------\
            'register' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/register',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => [
                        \Module\OAuth2\Actions\IOC::bareService()->RegisterPage,
                    ],
                ],
            ],
            'login' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/login',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => \Module\OAuth2\Actions\IOC::bareService()->LoginPage,
                ],
            ],
            'logout' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/logout',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::ACTIONS => \Module\OAuth2\Actions\IOC::bareService()->LogoutPage,
                ],
            ],

            ## User Area ---------------------------------------------------------------------------------\
            'me' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/me',
                    'match_whole' => false,
                ],
                'routes' => [
                    'profile' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => function() { return []; },
                        ],
                    ],

                ],
            ],


            ## Members Validation / Login Challenge ------------------------------------------------------\
            'recover' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/recover',
                    'match_whole' => false,
                ],
                'routes' => [
                    'validate' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // also "validation_code" exists in params and pass through actions as argument
                            'criteria'    => '/validate/:validation_code{{\w+}}',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                \Module\OAuth2\Actions\IOC::bareService()->ValidatePage,
                            ],
                        ],
                    ],
                    'validate_resend' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // also "validation_code" exists in params and pass through actions as argument
                            'criteria'    => '/validate/resend/:validation_code{{\w+}}/:identifier_type{{\w+}}',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                \Module\OAuth2\Actions\IOC::bareService()->ResendAuthCodeRequest,
                            ],
                        ],
                    ],

                    'signin_recognize' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // also "validation_code" exists in params and pass through actions as argument
                            'criteria'    => '/recognize',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                \Module\OAuth2\Actions\IOC::bareService()->SigninRecognizePage,
                            ],
                        ],
                    ],

                    'signin_challenge' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/challenge/:uid{{\w+}}[/:identifier{{\w+}}]',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                \Module\OAuth2\Actions\IOC::bareService()->SigninChallengePage,
                            ],
                        ],
                    ],

                    'pick_new_password' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/newpass/:validation_code{{\w+}}/:token{{\w+}}',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::ACTIONS => [
                                \Module\OAuth2\Actions\IOC::bareService()->SigninNewPassPage,
                            ],
                        ],
                    ],
                ],
            ],


            ## API ---------------------------------------------------------------------------------------\
            // TODO default renderer strategy for this routes
            'api' => include __DIR__.'/routes/api-routes.php',


        ], // end oauth routes
    ],
];
