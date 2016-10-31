<?php
/**
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return array(
    'services' => array(
        'Module\OAuth2\Actions\Authorize'
           => array(\Poirot\Ioc\Container\BuildContainer::NAME => 'Authorize'),
        'Module\OAuth2\Actions\RespondToRequest'
           => array(\Poirot\Ioc\Container\BuildContainer::NAME => 'RespondToRequest'),
        'Module\OAuth2\Actions\RetrieveAuthenticatedUser'
           => array(\Poirot\Ioc\Container\BuildContainer::NAME => 'RetrieveAuthenticatedUser'),
    ),
);
