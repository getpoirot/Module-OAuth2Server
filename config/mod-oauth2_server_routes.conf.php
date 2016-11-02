<?php
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;

return [
    'oauth'  => [
        'route' => 'RouteSegment',
        'options' => [
            'criteria'    => '/auth',
            'match_whole' => false,
        ],
        'routes' => [
            'authorize' => [
                'route' => 'RouteSegment',
                'options' => [
                    'criteria'    => '/',
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
