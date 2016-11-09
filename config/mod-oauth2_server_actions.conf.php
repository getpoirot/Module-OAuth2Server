<?php
/**
 *
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return [
    'services' => [
        // this class will registered as service by given name; exp. Authorize
        \Module\OAuth2\Actions\Login::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'Login'],

        \Module\OAuth2\Actions\Register::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'Register'],

        \Module\OAuth2\Actions\Authorize::class
           => [\Poirot\Ioc\Container\BuildContainer::NAME => 'Authorize'],

        \Module\OAuth2\Actions\RespondToRequest::class
           => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RespondToRequest'],


        \Module\OAuth2\Actions\AssertAuthToken::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'AssertAuthToken'],

        \Module\OAuth2\Actions\RetrieveAuthenticatedUser::class
           => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RetrieveAuthenticatedUser'],
    ],
];
