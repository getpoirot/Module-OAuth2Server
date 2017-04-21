<?php
namespace Module\OAuth2\Actions\Api;

use Module\Foundation\Actions\Helper\UrlAction;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Model\Entity;
use Poirot\Http\HttpMessage\Request\Plugin;
use Module\OAuth2\Actions\aApiAction;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\Interfaces\iHttpRequest;


class RegisterRequest
    extends aApiAction
{
    protected $tokenMustHaveOwner  = false;
    protected $tokenMustHaveScopes = array(

    );

    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * @param iRepoUsers           $users               @IoC /module/oauth2/services/repository/Users
     * @param iHttpRequest         $request             @IoC /
     */
    function __construct(iRepoUsers $users, iHttpRequest $request)
    {
        $this->repoUsers = $users;

        parent::__construct($request);
    }


    function __invoke($token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        $request = $this->request;

        if (! Plugin\MethodType::_($request)->isPost() )
            throw new \Exception('Bad Request', 400);


        # Create User Entity From Http Request
        #
        $hydrateUser = new Entity\UserHydrate(
            Entity\UserHydrate::parseWith($this->request) );

        $entityUser  = new Entity\UserEntity($hydrateUser);

        // check allow server to pick a username automatically if not given!!
        $config  = $this->sapi()->config()->get(\Module\OAuth2\Module::CONF_KEY);
        $isAllow = (boolean) $config['allow_server_pick_username'];

        if (! $entityUser->getUsername() && $isAllow) {
            // Give Registered User Default Username On Registration
            $username = $this->AttainUsername($entityUser);
            $entityUser->setUsername($username);
        }

        __( new Entity\UserValidate($entityUser, ['must_have_username' => true]) )
            ->assertValidate();

        # Register User:
        #
        // Continue Used to OAuth Registration Follow!!!
        $queryParams    = Plugin\ParseRequestData::_($request)->parseQueryParams();
        $continue       = (isset($queryParams['continue'])) ? $queryParams['continue'] : null;

        /** @var iOAuthUser $userEntity */
        list($userEntity, $validationHash) = $this->Register()->persistUser($entityUser, $continue);


        # Build Response:
        #
        $r = [
            'user' => [
                'uid'         => (string) $userEntity->getUid(),
                'fullname'    => $userEntity->getFullName(),
                'identifiers' => $userEntity->getIdentifiers(),
                'datetime_created' => [
                    'datetime'  => $userEntity->getDateCreated(),
                    'timestamp' => $userEntity->getDateCreated()->getTimestamp(),
                ],
            ],
        ];


        $resendLinks = []; $validateLinks = [];
        foreach ($userEntity->getIdentifiers() as $ident)
        {
            if ($ident->isValidated())
                continue;

            $resendLinks[$ident->getType()] = (string) $this->withModule('foundation')->url(
                'main/oauth/recover/validate_resend'
                , array('validation_code' => $validationHash, 'identifier_type' => $ident->getType())
            );
        }

        (! $validationHash ) ?: $r += [
            '_link' => [
                'validate' => (string) $this->withModule('foundation')->url(
                    'main/oauth/recover/validate'
                    , ['validation_code' => $validationHash]
                ),
                'validation_page' => (string) $this->withModule('foundation')->url(
                    'main/oauth/recover/validate'
                    , array('validation_code' => $validationHash)
                ),
                'resend_authcode' => $resendLinks,
            ],
        ];

        return [
            ListenerDispatch::RESULT_DISPATCH => $r
        ];
    }
}
