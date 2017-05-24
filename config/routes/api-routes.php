<?php
use Module\HttpFoundation\Events\Listener\ListenerDispatch;

return [
    'route' => 'RouteSegment',
    'options' => [
        'criteria'    => '/api/v1',
        'match_whole' => false,
    ],
    'params'  => [
        ListenerDispatch::ACTIONS => [
            // This Action Run First In Chains and Assert Validate Token
            //! define array allow actions on matched routes chained after this action
            /*
             * [
             *    [0] => Callable Defined HERE
             *    [1] => routes defined callable
             *     ...
             */
            '/module/oauth2client/actions/AssertToken',
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
            'routes' => [
                ## My Profile:
                'profile' => [
                    'route' => 'RouteSegment',
                    'options' => [
                        'criteria'    => '/profile',
                        'match_whole' => true,
                    ],
                    'params'  => [
                        ListenerDispatch::ACTIONS => [
                            '/module/oauth2/actions/GetUserInfoRequest'
                        ],
                    ],
                ],

                ## Grants (password):
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
                                ListenerDispatch::ACTIONS => [
                                    '/module/oauth2/actions/ChangePasswordRequest',
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
                                ListenerDispatch::ACTIONS => [
                                    '/module/oauth2/actions/ChangeIdentityRequest',
                                ]
                            ],
                        ],

                        // Confirm Identity Validation:
                        'confirm' => [
                            'route' => 'RouteSegment',
                            'options' => [
                                'criteria'    => '/change/confirm/:validation_code~\w+~',
                                'match_whole' => true,
                            ],
                            'params'  => [
                                ListenerDispatch::ACTIONS => [
                                    '/module/oauth2/actions/ConfirmValidationRequest',
                                ],
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
                        ListenerDispatch::ACTIONS => [
                            '/module/oauth2/actions/isExistsUserWithIdentifierRequest',
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
                        ListenerDispatch::ACTIONS => '/module/oauth2/actions/WhoisRequest',
                    ],
                ],
                'profile' => [
                    'route' => 'RouteSegment',
                    'options' => [
                        // TODO . in username not matched
                        'criteria'    => '/profile/<@:username~\w+~><:userid~\w{24}~>',
                        'match_whole' => true,
                    ],
                    'params'  => [
                        ListenerDispatch::ACTIONS => '/module/oauth2/actions/GetUserInfoRequest',
                    ],
                ],
                // Register New User Request By POST
                'post' => [
                    'route'   => 'RouteMethod',
                    'options' => [
                        'method' => 'POST',
                    ],
                    'params'  => [
                        ListenerDispatch::ACTIONS => [
                            '/module/oauth2/actions/RegisterRequest',
                        ],
                    ],
                ],
            ],
        ]
    ],
];