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
                // TODO preserve url query params with redirect

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
                            'identifiers' => [
                                'route' => 'RouteSegment',
                                'options' => [
                                    'criteria'    => '/identifiers',
                                    'match_whole' => false,
                                ],
                                'routes' => [
                                    // Change Password:
                                    'change_pass' => [
                                        'route' => 'RouteSegment',
                                        'options' => [
                                            'criteria'    => '/change_pass',
                                            'match_whole' => true,
                                        ],
                                        'params'  => [
                                            ListenerDispatch::CONF_KEY => [
                                                \Module\OAuth2\Actions\Users\ChangePassword::getParsedRequestDataClosure(),
                                                \Module\OAuth2\Actions\Users\ChangePassword::getParsedUIDFromTokenClosure(),
                                                '/module/oauth2/actions/users/ChangePassword',
                                            ],
                                        ],
                                    ],
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
            'validate' => [
                'route' => 'RouteSegment',
                'options' => [
                    // also "validation_code" exists in params and pass through actions as argument
                    'criteria'    => '/members/validate/:validation_code{\w+}',
                    'match_whole' => true,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Users/ValidatePage',
                ],
            ],

            ##
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
