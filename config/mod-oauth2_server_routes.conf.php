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
        /* prefixed uri
         'route' => 'RouteSegment',
            'options' => [
                'criteria'    => '/auth',
                'match_whole' => false,
             ],*/
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
                    'criteria'    => '/token',
                ],
                'params'  => [
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/RespondToRequest',
                ],
            ],
        ],
    ],
];
