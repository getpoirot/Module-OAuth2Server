<?php
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;

return array(
    'oauth'  => array(
        'route' => 'RouteSegment',
        'options' => array(
            'criteria'    => '/oauth',
            'match_whole' => false,
        ),
        'params'  => array(
            ListenerDispatch::CONF_KEY => function()
            {
                k('OAuth');
            },
        ),
        'routes' => array(
            'authorize' => array(
                'route' => 'RouteSegment',
                'options' => array(
                    'criteria'    => '/authorize',
                ),
                'params'  => array(
                    ListenerDispatch::CONF_KEY => function()
                    {
                        kd('Authorize');
                    },
                ),
            ),
            'token' => array(
                'route' => 'RouteSegment',
                'options' => array(
                    'criteria'    => '/token',
                ),
                'params'  => array(
                    ListenerDispatch::CONF_KEY => function()
                    {
                        kd('Token');
                    },
                ),
            ),
        ),
    ),
);
