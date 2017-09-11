<?php
namespace Module\OAuth2\Actions\Api;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\OAuth2\Actions\aApiAction;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Module\OAuth2\Model\Entity\UserEntity;
use Module\OAuth2\Model\Entity\Validation\AuthObject;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class ValidationIdentifierRequest
    extends aApiAction
{
    protected $tokenMustHaveOwner  = false;
    protected $tokenMustHaveScopes = array(

    );

    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * Construct
     *
     * @param iRepoUsers           $users       @IoC /module/oauth2/services/repository/Users
     * @param iHttpRequest         $httpRequest @IoC /HttpRequest
     */
    function __construct(iRepoUsers $users, iHttpRequest $httpRequest)
    {
        $this->repoUsers = $users;

        parent::__construct($httpRequest);
    }


    /**
     * Get current user info identified with given token
     *
     * @param iEntityAccessToken $token
     * @param string             $userid
     * @param string|null        $identifier Specific user identifier
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null, $userid = null, $identifier = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Retrieve User With Given ID
        #
        $userid     = trim($userid);
        $userEntity = $this->repoUsers->findOneByUID($userid);

        /** @var UserEntity $userEntity */
        if (! $userEntity )
            throw new \Exception('User not Found.', 404);



        # Generate Validation Code For Given Identifier(s)
        #
        $generateFor = [];
        if ($identifier)
            $generateFor[] = $userEntity->getIdentifiers($identifier);
        else
            $generateFor   = $userEntity->getIdentifiers();

        /** @var IdentifierObject $identifierObject */
        foreach ($generateFor as $i => $identifierObject) {
            // Clean Validated Identifier(s)
            if ($identifierObject->isValidated())
                unset($generateFor[$i]);
        }

        $entityValidation = $this->Validation()->madeValidationChallenge($userEntity, $generateFor);
        $validationHash   = ($entityValidation) ? $entityValidation->getValidationCode() : null;


        # Send Validation Code To Medium
        # - if only specific identifier is given
        #
        if ($entityValidation && $identifier) {
            /** @var AuthObject $authCodeObject */
            foreach ($entityValidation->getAuthCodes() as $authCodeObject)
                $_ = $this->Validation()->sendAuthCodeByMediumType($entityValidation, $authCodeObject->getType());
        }


        # Build Response
        #
        $validations = [];
        foreach ($generateFor as $ident)
        {
            $type         = $ident->getType();

            $linkValidate = (string) \Module\HttpFoundation\Actions::url(
                'main/oauth/recover/validate'
                , ['validation_code' => $validationHash]
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
                    'validate'  => $linkValidate.'?'.$type.'=***',
                    'send_code' => $linkResend,
                ],
            ];
        }

result:
        $r = [
            'validations' => $validations,
        ];

        (! $entityValidation ) ?: $r += [
            'hash' => $validationHash,
            '_link' => [
                'validation_page' => (string) \Module\HttpFoundation\Actions::url(
                    'main/oauth/recover/validate'
                    , ['validation_code' => $validationHash]
                ),
            ],
        ];

        return [
            ListenerDispatch::RESULT_DISPATCH => $r,
        ];
    }
}
