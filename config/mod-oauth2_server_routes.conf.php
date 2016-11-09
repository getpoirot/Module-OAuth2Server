<?php
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;

return [
    // Override Home Page Route
    'home'  => array(
        'route'    => 'RouteSegment',
        'options' => array(
            'criteria'    => '/',
            'match_whole' => true,
        ),
        'params'  => array(
            ListenerDispatch::CONF_KEY => function() {
                header('Location: '. \Module\Foundation\Actions\IOC::url('main/oauth/login'));
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
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/AssertAuthToken'
                ],
                'routes' => [
                    'register' => [
                        'route' => 'RouteSegment',
                        'options' => [
                            'criteria'    => '/register',
                            'match_whole' => false,
                        ],
                        'params'  => [
                            ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Register',
                        ],
                    ]
                ],
            ],

            ##
            'register' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/register',
                    'match_whole' => false,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Register',
                ],
            ],
            'login' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/login',
                    'match_whole' => false,
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Login',
                ],
            ],
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
