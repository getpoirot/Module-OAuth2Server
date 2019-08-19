<?php
use \Module\OAuth2\Services\Grants;
use \Poirot\OAuth2\Server\Grant;

return [
    // Server Automatically Choose a Username For Registered User If Not Sent
    'allow_server_pick_username'          => true,
    'allow_change_identifier_immediately' => false,

    'mediums' => [
        // TODO use %auth_code% instead of %s
        'mobile' => [
            // Path to Template file or String
            'message_verification' => 'کد فعال سازی شما %s',
            'alter_send_method'    => function (iClientOfSMS $smsClient, $mobileNo, $code) {
                // Currently our sms provider support for sending verification codes; with higher priority and delivery!
                return $smsClient->sendVerificationTo($mobileNo, 'papionVerify', ['token' => $code]);
            }
        ],
        'email' => [
            // Path to Template file or String
            'message_verification' => '',
        ],
    ],

    // Capped Container Plugins Of Available Grants
    //
    'grants' => [
        'plugins' => [
            'services' => [
                Grant\GrantAuthCode::GrantType => Grants\GrantAuthorizationCodeService::class,
                Grant\GrantImplicit::GrantType => Grants\GrantImplicitService::class,
                Grant\GrantClientCredentials::GrantType => Grants\GrantClientCredentialService::class,
                Grant\GrantPassword::GrantType => Grants\GrantPasswordService::class,
                Grant\GrantRefreshToken::GrantType => Grants\GrantRefreshTokenService::class,
                // Grant Extension To Validate Http Token
                Grants\GrantValidationExtensionService::GrantType => Grants\GrantValidationExtensionService::class,
            ],
        ],
        'settings' => [
            /** @see Grants\aGrantService */
            'default' => [
            ],
            'authorization_code' => [
            ],
        ],
    ],

    # Authorization:

    \Module\Authorization\Module::CONF => [
        ServiceAuthenticatorsContainer::CONF => [
            'plugins_container' => [
                'services' => [
                    // Authenticators Services
                    OAuth2\Services\ServiceAuthenticatorDefault::class,
                ],
            ],
        ],
        ServiceGuardsContainer::CONF => [
            'plugins_container' => [
                'services' => [
                    // Guards Services
                    'oauth_routes' => OAuth2\Services\ServiceAuthGuard::class,
                ],
            ],
        ],
    ],
];
