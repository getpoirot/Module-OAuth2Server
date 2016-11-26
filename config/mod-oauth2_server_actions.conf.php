<?php
/**
 *
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return [

    'nested' => [
        'Users' => [
            'services' => [
                // this class will registered as service by given name; exp. Login as Service
                \Module\OAuth2\Actions\Users\LoginPage::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'LoginPage'],

                \Module\OAuth2\Actions\Users\LogoutPage::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'LogoutPage'],

                \Module\OAuth2\Actions\Users\RegisterPage::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RegisterPage'],

                \Module\OAuth2\Actions\Users\Register::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'Register'],

                \Module\OAuth2\Actions\Users\ValidatePage::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'ValidatePage'],

                \Module\OAuth2\Actions\Users\RetrieveAuthenticatedUser::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RetrieveAuthenticatedUser'],
            ], ], ],

    'services' => [
        \Module\OAuth2\Actions\Authorize::class
           => [\Poirot\Ioc\Container\BuildContainer::NAME => 'Authorize'],

        \Module\OAuth2\Actions\RespondToRequest::class
           => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RespondToRequest'],
    ],
];
