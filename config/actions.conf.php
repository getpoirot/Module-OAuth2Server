<?php
/**
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return [
    'services' => [
        // this class will registered as service by given name; exp. Authorize
        \Module\OAuth2\Actions\Authorize::class
           => [\Poirot\Ioc\Container\BuildContainer::NAME => 'Authorize'],

        \Module\OAuth2\Actions\RespondToRequest::class
           => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RespondToRequest'],

        \Module\OAuth2\Actions\RetrieveAuthenticatedUser::class
           => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RetrieveAuthenticatedUser'],
    ],
];
