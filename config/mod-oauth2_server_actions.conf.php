<?php
/**
 *
 * @see \Poirot\Ioc\Container\BuildContainer
 */
return [
    'services' => [
        'RegisterPage'          => \Module\OAuth2\Actions\User\RegisterPage::class,
        'Register'              => \Module\OAuth2\Actions\User\Register::class,
        'LoginPage'             => \Module\OAuth2\Actions\User\LoginPage::class,
        'LogoutPage'            => \Module\OAuth2\Actions\User\LogoutPage::class,

        'ValidatePage'                => \Module\OAuth2\Actions\Validation\ValidatePage::class,
        'Validation'                  => \Module\OAuth2\Actions\Validation\Validation::class,
        'ResendAuthCodeRequest'       => \Module\OAuth2\Actions\Validation\ResendAuthCodeRequest::class,
        'ValidationIdentifierRequest' => \Module\OAuth2\Actions\Api\ValidationIdentifierRequest::class,

        'SigninRecognizePage'   => \Module\OAuth2\Actions\Recover\SigninRecognizePage::class,
        'SigninChallengePage'   => \Module\OAuth2\Actions\Recover\SigninChallengePage::class,
        'SigninNewPassPage'     => \Module\OAuth2\Actions\Recover\SigninNewPassPage::class,


        'AuthorizePage'             => \Module\OAuth2\Actions\AuthorizePage::class,
        'RespondToTokenRequest'     => \Module\OAuth2\Actions\RespondToTokenRequest::class,

        'GrantResponder'            => \Module\OAuth2\Actions\GrantResponder::class,
        'RetrieveAuthenticatedUser' => \Module\OAuth2\Actions\User\RetrieveAuthenticatedUser::class,
        'AttainUsername'            => \Module\OAuth2\Actions\Helper\AttainUsername::class,
        'GenIdentifierAuthCode'     => \Module\OAuth2\Actions\Validation\GenIdentifierAuthCode::class,


        'RegisterRequest'          => \Module\OAuth2\Actions\Api\RegisterRequest::class,
        'GetUserInfoRequest'       => \Module\OAuth2\Actions\Api\GetUserInfoRequest::class,
        'ListUsersInfoRequest'     => \Module\OAuth2\Actions\Api\ListUsersInfoRequest::class,
        'ChangePasswordRequest'    => \Module\OAuth2\Actions\Api\ChangePasswordRequest::class,
        'ChangeIdentityRequest'    => \Module\OAuth2\Actions\Api\ChangeIdentityRequest::class,
        'ConfirmValidationRequest' => \Module\OAuth2\Actions\Api\ConfirmValidationRequest::class,
        'WhoisRequest'             => \Module\OAuth2\Actions\Api\WhoisRequest::class,
        'isExistsUserWithIdentifierRequest' => \Module\OAuth2\Actions\Api\isExistsUserWithIdentifierRequest::class,

    ],
];
