<?php
namespace Module\OAuth2\Actions\Api;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\OAuth2\Actions\aApiAction;
use Module\OAuth2\Exception\exPasswordNotMatch;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Module\OAuth2\Model\Entity\UserEntity;
use Module\OAuth2\Model\Entity\Validation\AuthObject;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;


class ChangePasswordRequest
    extends aApiAction
{
    const REASON_CHANGE_PASSWORD = 'change.password';

    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * @param iRepoUsers           $users       @IoC /module/oauth2/services/repository/Users
     * @param iHttpRequest         $httpRequest @IoC /HttpRequest
     */
    function __construct(iRepoUsers $users, iHttpRequest $httpRequest)
    {
        $this->repoUsers = $users;

        parent::__construct($httpRequest);
    }


    /**
     * Change Password Grant Credential For Current User Determined By Token
     *
     * @param iEntityAccessToken $token
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Retrieve User With OwnerID
        #
        /** @var UserEntity|iOAuthUser $userEntity */
        if (! $userEntity = $this->repoUsers->findOneByUID( $token->getOwnerIdentifier() ))
            throw new \Exception('User not Found.', 500);


        # Validate Sent Data:
        $post = ParseRequestData::_($this->request)->parse();
        $post = $this->_assertValidData($post);


        // Current password must match
        //
        if (isset($post['currpass']) && null !== $currPasswd = $userEntity->getPassword() ) {
            if ($this->repoUsers->makeCredentialHash($post['currpass']) !== $currPasswd)
                throw new exPasswordNotMatch('Current Password Does not match!');

            $r = $this->repoUsers->updateGrantTypeValue($token->getOwnerIdentifier(), 'password', $post['newpass']);


            ## Build Response
            #
            return [
                ListenerDispatch::RESULT_DISPATCH => [
                    'stat' => ($r) ? 'changed' : 'unchanged'
                ],
            ];
        }


        // No Password Defined Yet!!
        //
        /** @var IdentifierObject $mobIdentifier */
        $mobIdentifier = $userEntity->getIdentifiers(IdentifierObject::IDENTITY_MOBILE);
        if (! $mobIdentifier )
            throw new exUnexpectedValue('"currpass" Parameters is missing.');


        // TODO persist change password validation entity only once

        // force send validation challenge
        /** @see Validation::madeValidationChallenge */
        $mobIdentifier->setValidated(false);
        $entityValidation = $this->Validation()
            ->madeValidationChallenge($userEntity, [$mobIdentifier], null, null, self::REASON_CHANGE_PASSWORD, ['password_change' => $post['newpass']]);
        /** @var AuthObject $authCodeObject */
        foreach ($entityValidation->getAuthCodes() as $authCodeObject)
            $_ = $this->Validation()->sendAuthCodeByMediumType($entityValidation, $authCodeObject->getType());



        $validations = []; $generateFor = [$mobIdentifier];
        foreach ($generateFor as $ident)
        {
            $type         = $ident->getType();

            $linkValidate = (string) \Module\HttpFoundation\Actions::url(
                'main/oauth/api/me/identifiers/confirm'
                , ['validation_code' => $entityValidation->getValidationCode()]
            );

            $linkResend   = (string) \Module\HttpFoundation\Actions::url(
                'main/oauth/recover/validate_resend'
                , [
                    'validation_code' => $entityValidation->getValidationCode(),
                    'identifier_type' => $ident->getType()
                ]
            );

            $validations[$type] = [
                '_link' => [
                    'validate'  => $linkValidate,
                    'send_code' => $linkResend,
                ],
            ];
        }

        $r = [
            'validations' => $validations,
        ];

        (! $entityValidation ) ?: $r += [
            'hash' => $entityValidation->getValidationCode(),
            '_link' => [
                'validation_page' => (string) \Module\HttpFoundation\Actions::url(
                    'main/oauth/recover/validate'
                    , ['validation_code' => $entityValidation->getValidationCode()]
                ),
            ],
        ];

        return [
            ListenerDispatch::RESULT_DISPATCH => $r
        ];
        // $r = $this->repoUsers->updateGrantTypeValue($token->getOwnerIdentifier(), 'password', $post['newpass']);
    }


    // ..

    /**
     * Assert Validated Change Password Post Data
     *
     * Array (
     *   [credential] => e10adc3949ba59abbe56e057f20f883e
     * )
     *
     * @param array $post
     *
     * @return array
     */
    protected function _assertValidData(array $post)
    {
        # Validate Data:
        if (! isset($post['newpass']) )
            throw new exUnexpectedValue('Arguments "newpass" is required.');


        # Sanitize Data:
        $post['newpass']  = trim($post['newpass']);
        $post['currpass'] = (isset($post['currpass']) && !empty($post['currpass'])) ? $post['currpass'] : null;

        return $post;
    }
}
