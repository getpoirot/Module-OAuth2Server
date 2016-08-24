<?php
/**
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return array(
    'services' => array(
        'Module\OAuth2\Actions\Authorize' => array(':name' => 'Authorize'),
        'Module\OAuth2\Actions\RespondToRequest' => array(':name' => 'RespondToRequest'),
        'Module\OAuth2\Actions\RetrieveAuthenticatedUser' => array(':name' => 'RetrieveAuthenticatedUser'),
    ),
);
