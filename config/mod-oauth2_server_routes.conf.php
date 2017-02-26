<?php
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;

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
            ListenerDispatch::CONF_KEY => function() {
                return new \Module\Foundation\HttpSapi\Response\ResponseRedirect(
                    \Module\Foundation\Actions\IOC::url('main/oauth/login')
                );
            },
        ),
    ),

    'oauth'  => [
        'routes' => [

            ## API
            'api' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/api/v1',
                    'match_whole' => false,
                ],
                'params'  => [
                    // This Action Run First In Chains and Assert Validate Token
                    //! define array allow actions on matched routes chained after this action
                    /*
                     * [
                     *    [0] => Callable Defined HERE
                     *    [1] => routes defined callable
                     *     ...
                     */
                    ListenerDispatch::CONF_KEY => [
                        function ($request = null) {
                            $token = \Module\OAuth2\assertAuthToken($request);
                            return ['token' => $token];
                        }
                    ],
                ],
                'routes' => [
                    ## me
                    'me' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/me',
                            'match_whole' => false,
                        ],
                        // TODO only tokens that has owner identifier
                        'routes' => [
                            ##Profile:
                            'profile' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/profile',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/users/GetUserInfo',
                                ],
                            ],
                            ## Identifiers:
                            'grants' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/grants',
                                    'match_whole' => false,
                                ],
                                'routes' => [
                                    // Change Password:
                                    'password' => [
                                        'route' => 'RouteSegment',
                                        'options' => [
                                            'criteria'    => '/password',
                                            'match_whole' => true,
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                \Module\OAuth2\Actions\Users\ChangePassword::getParsedUIDFromTokenClosure(),
                                                \Module\OAuth2\Actions\Users\ChangePassword::getParsedRequestDataClosure(),
                                                '/module/oauth2/actions/users/ChangePassword',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            ## Identifiers:
                            'identifiers' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/identifiers',
                                    'match_whole' => false,
                                ],
                                'routes' => [
                                    // Identifiers:
                                    // Change Identity (email, mobile, ..):
                                    'change' => [
                                        'route' => 'RouteSegment',
                                        'options' => [
                                            'criteria'    => '/change',
                                            'match_whole' => true,
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                \Module\OAuth2\Actions\Users\ChangeIdentity::getParsedRequestDataClosure(),
                                                \Module\OAuth2\Actions\Users\ChangeIdentity::getParsedUIDFromTokenClosure(),
                                                '/module/oauth2/actions/users/ChangeIdentity',
                                            ]
                                        ],
                                    ],
                                    // Confirm Identity Validation:
                                    'confirm' => [
                                        'route' => 'RouteSegment',
                                        'options' => [
                                            'criteria'    => '/change/confirm/:validation_code{\w+}',
                                            'match_whole' => true,
                                        ],
                                        'params'  => [
                                            // TODO separate Page with API func.
                                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/ValidatePage'
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],

                    ## members
                    'members' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/members',
                            'match_whole' => false,
                        ],
                        'routes' => [
                            'exists' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/exists',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => [
                                        \Module\OAuth2\Actions\Users\isExistsUserWithIdentifier::getParsedRequestDataClosure(),
                                        '/module/oauth2/actions/users/isExistsUserWithIdentifier',
                                    ],
                                ],
                            ],
                            'whois' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/whois',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/users/WhoisRequest',
                                ],
                            ],
                            'profile' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/profile/:uid{\w+}',
                                    'match_whole' => true,
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/users/GetUserInfo',
                                ],
                            ],
                            // When POST something
                            'post' => [
                                'route'   => 'RouteMethod',
                                'options' => [
                                    'method' => 'POST',
                                ],
                                'params'  => [
                                    ListenerDispatch::CONF_KEY => [
                                        '/module/oauth2/actions/users/RegisterRequest',
                                    ],
                                ],
                            ],
                        ],
                    ]
                ],
            ],

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
                            ListenerDispatch::CONF_KEY => function() { return []; },
                        ],
                    ],

                ],
            ],

            ## members
            'members' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/members',
                    'match_whole' => false,
                ],
                'routes' => [
                    'validate' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // also "validation_code" exists in params and pass through actions as argument
                            'criteria'    => '/validate/:validation_code{\w+}',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/ValidatePage',
                        ],
                    ],
                    'validate_resend' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // also "validation_code" exists in params and pass through actions as argument
                            'criteria'    => '/validate/resend/:validation_code{\w+}/:identifier_type{\w+}',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/ValidationResendAuthCode',
                        ],
                    ],

                    'signin_recognize' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            // also "validation_code" exists in params and pass through actions as argument
                            'criteria'    => '/signin/recognize',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/SigninRecognizePage',
                        ],
                    ],

                    'signin_challenge' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/signin/challenge/:uid{\w+}[/:identifier{\w+}]',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/SigninChallengePage',
                        ],
                    ],

                    'pick_new_password' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/signin/newpass/:validation_code{\w+}/:token{\w+}',
                            'match_whole' => true,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/SigninNewPassPage',
                        ],
                    ],

                ],
            ],


            ##
            'register' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/register',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/RegisterPage',
                ],
            ],
            'login' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/login',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/LoginPage',
                ],
            ],
            'logout' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/logout',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/LogoutPage',
                ],
            ],



            ## OAuth2 EndPoints:

            'authorize' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/auth',
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Authorize',
                ],
            ],
            'token' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/auth/token',
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/RespondToRequest',
                ],
            ],

        ],
    ],
];
