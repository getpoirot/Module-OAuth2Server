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

                \Module\OAuth2\Actions\Users\SigninRecognizePage::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'SigninRecognizePage'],

                \Module\OAuth2\Actions\Users\SigninChallengePage::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'SigninChallengePage'],

                \Module\OAuth2\Actions\Users\RegisterPage::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RegisterPage'],

                \Module\OAuth2\Actions\Users\RegisterRequest::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RegisterRequest'],

                \Module\OAuth2\Actions\Users\isExistsUserWithIdentifier::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'isExistsUserWithIdentifier'],

                \Module\OAuth2\Actions\Users\WhoisRequest::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'WhoisRequest'],

                \Module\OAuth2\Actions\Users\Register::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'Register'],

                \Module\OAuth2\Actions\Users\GetUserInfo::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'GetUserInfo'],

                \Module\OAuth2\Actions\Users\ChangePassword::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'ChangePassword'],

                \Module\OAuth2\Actions\Users\ChangeIdentity::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'ChangeIdentity'],

                \Module\OAuth2\Actions\Users\ValidatePage::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'ValidatePage'],
                \Module\OAuth2\Actions\Users\ValidationResendAuthCodeAction::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'ValidationResendAuthCode'],


                \Module\OAuth2\Actions\Users\ValidationGenerator::class
                => [\Poirot\Ioc\Container\BuildContainer::NAME => 'ValidationGenerator'],

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
