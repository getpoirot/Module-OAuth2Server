<?php
/**
 *
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return [
    'services' => [
        'RegisterPage'          => \Module\OAuth2\Actions\User\RegisterPage::class,
        'Register'              => \Module\OAuth2\Actions\User\Register::class,

        'ValidatePage'          => \Module\OAuth2\Actions\Validation\ValidatePage::class,
        'ResendAuthCodeRequest' => \Module\OAuth2\Actions\Validation\ResendAuthCodeRequest::class,

        'MadeUserIdentifierValidationState' => \Module\OAuth2\Actions\Validation\MadeUserIdentifierValidationState::class,
        'GenIdentifierAuthCode'             => \Module\OAuth2\Actions\Validation\GenIdentifierAuthCode::class,


        'AttainUsername'        => \Module\OAuth2\Actions\Helper\AttainUsername::class,



        \Module\OAuth2\Actions\Users\LoginPage::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'LoginPage'],

        \Module\OAuth2\Actions\Users\LogoutPage::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'LogoutPage'],

        \Module\OAuth2\Actions\Users\SigninRecognizePage::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'SigninRecognizePage'],

        \Module\OAuth2\Actions\Users\SigninChallengePage::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'SigninChallengePage'],

        \Module\OAuth2\Actions\Users\SigninNewPassPage::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'SigninNewPassPage'],


        \Module\OAuth2\Actions\Users\RegisterRequest::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RegisterRequest'],

        \Module\OAuth2\Actions\Users\isExistsUserWithIdentifier::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'isExistsUserWithIdentifier'],

        \Module\OAuth2\Actions\Users\WhoisRequest::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'WhoisRequest'],


        \Module\OAuth2\Actions\Users\GetUserInfo::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'GetUserInfo'],

        \Module\OAuth2\Actions\Users\ChangePassword::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'ChangePassword'],

        \Module\OAuth2\Actions\Users\ChangeIdentity::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'ChangeIdentity'],

        \Module\OAuth2\Actions\Users\RetrieveAuthenticatedUser::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RetrieveAuthenticatedUser'],




        \Module\OAuth2\Actions\Authorize::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'Authorize'],

        \Module\OAuth2\Actions\RespondToRequest::class
        => [\Poirot\Ioc\Container\BuildContainer::NAME => 'RespondToRequest'],
    ],
];
