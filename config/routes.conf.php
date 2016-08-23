<?php
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;

return array(
    'oauth'  => array(
        'route' => 'RouteSegment',
        'options' => array(
            'criteria'    => '/auth',
            'match_whole' => false,
        ),
        'routes' => array(
            'authorize' => array(
                'route' => 'RouteSegment',
                'options' => array(
                    'criteria'    => '/',
                ),
                'params'  => array(
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/Authorize',
                ),
            ),
            'token' => array(
                'route' => 'RouteSegment',
                'options' => array(
                    'criteria'    => '/token',
                ),
                'params'  => array(
                    ListenerDispatch::CONF_KEY => '/module/oauth2/actions/RespondToRequest',
                ),
            ),
        ),
    ),
);
