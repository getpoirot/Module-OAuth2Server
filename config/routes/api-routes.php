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
            '/module/oauth2client/actions/AssertToken' => 'token',
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
                        ListenerDispatch::ACTIONS => [ '/module/oauth2/actions/WhoisRequest' ],
                    ],
                ],
                'listProfiles' => [
                    'route' => 'RouteSegment',
                    'options' => [
                        'criteria'    => '/profiles',
                        'match_whole' => true,
                    ],
                    'params'  => [
                        ListenerDispatch::ACTIONS => [ '/module/oauth2/actions/ListUsersInfoRequest' ],
                    ],
                ],
                'lists' => [
                    'route' => 'RouteSegment',
                    'options' => [
                        'criteria'    => '/list',
                        'match_whole' => true,
                    ],
                    'params'  => [
                        ListenerDispatch::ACTIONS => [ \Module\OAuth2\Actions\Api\ListUsersRequest::class, ],
                    ],
                ],
                'delegate' => [
                    'route' => 'RouteSegment',
                    'options' => [
                        'criteria'    => '/<:userid~\w{24}~>',
                        'match_whole' => false,
                    ],
                    'routes' => [
                        'validate' => [
                            'route' => 'RouteSegment',
                            'options' => [
                                'criteria'    => '/validate</:identifier~\w+~>',
                                'match_whole' => true,
                            ],
                            'params'  => [
                                ListenerDispatch::ACTIONS => [ '/module/oauth2/actions/ValidationIdentifierRequest' ],
                            ],
                        ],
                    ],
                ],
                'profile' => [
                    'route' => 'RouteSegment',
                    'options' => [
                        'criteria'    => '/profile/<u/:username~[a-zA-Z0-9._]+~><-:userid~\w{24}~>',
                        'match_whole' => true,
                    ],
                    'params'  => [
                        ListenerDispatch::ACTIONS => [ '/module/oauth2/actions/GetUserInfoRequest' ],
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