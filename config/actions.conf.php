<?php
/**
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return array(
    'services' => array(
        'Module\OAuth2\Actions\Authorize' => array(':name' => 'authorize'),
        'Module\OAuth2\Actions\RespondToRequest' => array(':name' => 'RespondToRequest'),
    ),
);
