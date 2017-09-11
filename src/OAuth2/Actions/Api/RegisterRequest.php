<?php
namespace Module\OAuth2\Actions\Api;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Model\Entity;
use Poirot\Http\HttpMessage\Request\Plugin;
use Module\OAuth2\Actions\aApiAction;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;


class RegisterRequest
    extends aApiAction
{
    protected $tokenMustHaveOwner  = false;
    protected $tokenMustHaveScopes = array(

    );

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


    function __invoke($token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        $request = $this->request;

        if (! Plugin\MethodType::_($request)->isPost() )
            throw new \Exception('Bad Request', 400);


        try {

            # Create User Entity From Http Request
            #
            $hydrateUser = new Entity\UserHydrate(
                Entity\UserHydrate::parseWith($this->request) );

            /** @var iEntityAccessToken $token */
            $hydrateUser->setClient( $token->getClientIdentifier() );
            $entityUser  = new Entity\UserEntity($hydrateUser);


            # Register User:
            #
            // Continue Used to OAuth Registration Follow!!!
            $queryParams    = Plugin\ParseRequestData::_($request)->parseQueryParams();
            $continue       = (isset($queryParams['continue'])) ? $queryParams['continue'] : null;

            /** @var iOAuthUser $userEntity */
            $entityUser = $this->Register()->persistUser($entityUser);

            # Give User Validation Code:
            #
            $validationHash = $this->Register()->giveUserValidationCode($entityUser, $continue);

        } catch (exUnexpectedValue $e)
        {
            // TODO Handle Validation ...
            throw new exUnexpectedValue('Validation Failed', null,  400, $e);
        }


        # Build Response:
        #
        $userInfo = [];

        $isValidAll = true; $validated = array();
        foreach ($userEntity->getIdentifiers() as $identifier) {
            // embed identifiers validity
            $isValidAll &= $identifier->isValidated();
            $validated[$identifier->getType()] = (boolean) $identifier->isValidated();

            $userInfo[$identifier->getType()] = $identifier->getValue();
        }

        $userInfo['meta'] = $userEntity->getMeta();

        $userInfo['datetime_created']  = [
            'datetime'  => $userEntity->getDateCreated(),
            'timestamp' => $userEntity->getDateCreated()->getTimestamp(),
        ];

        $r = [
            'user' => [
                'uid'         => (string) $userEntity->getUid(),
                'fullname'    => $userEntity->getFullName(),
            ] + $userInfo ,
        ];


        $resendLinks = [];
        foreach ($userEntity->getIdentifiers() as $ident)
        {
            if ($ident->isValidated())
                continue;

            $resendLinks[$ident->getType()] = (string) \Module\HttpFoundation\Actions::url(
                'main/oauth/recover/validate_resend'
                , array('validation_code' => $validationHash, 'identifier_type' => $ident->getType())
            );
        }

        (! $validationHash ) ?: $r += [
            'validation' => [
                'hash' => $validationHash,
                '_link' => [
                    'validate' => (string) \Module\HttpFoundation\Actions::url(
                        'main/oauth/recover/validate'
                        , ['validation_code' => $validationHash]
                    ),
                    'validation_page' => (string) \Module\HttpFoundation\Actions::url(
                        'main/oauth/recover/validate'
                        , array('validation_code' => $validationHash)
                    ),
                    'resend_authcode' => $resendLinks,
                ],
            ],
        ];


        return [
            ListenerDispatch::RESULT_DISPATCH => $r
        ];
    }
}
