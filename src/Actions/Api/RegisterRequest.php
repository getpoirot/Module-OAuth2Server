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
            /** @var iOAuthUser $userEntity */
            $entityUser = $this->Register()->persistUser($entityUser);

            /*
            # Give User Validation Code:
            #
            // Continue Used to OAuth Registration Follow!!!
            $queryParams    = Plugin\ParseRequestData::_($request)->parseQueryParams();
            $continue       = (isset($queryParams['continue'])) ? $queryParams['continue'] : null;

            $validationHash = $this->Register()->giveUserValidationCode($entityUser, $continue);
            */

        } catch (exUnexpectedValue $e)
        {
            // TODO Handle Validation ...
            throw new exUnexpectedValue('Validation Failed', null,  400, $e);
        }


        # Build Response:
        #
        $userInfo = [];

        $isValidAll = true; $validated = array();
        foreach ($entityUser->getIdentifiers() as $identifier) {
            // embed identifiers validity
            $isValidAll &= $identifier->isValidated();
            $validated[$identifier->getType()] = (boolean) $identifier->isValidated();

            $userInfo[$identifier->getType()] = $identifier->getValue();
        }

        $userInfo['meta'] = $entityUser->getMeta();

        $userInfo['datetime_created']  = [
            'datetime'  => $entityUser->getDateCreated(),
            'timestamp' => $entityUser->getDateCreated()->getTimestamp(),
        ];

        $r = [
            'user' => [
                'uid'         => (string) $entityUser->getUid(),
                'fullname'    => $entityUser->getFullName(),
            ] + $userInfo ,
            '_link' => [
                'validation' => (string) \Module\HttpFoundation\Actions::url(
                    'main/oauth/api/members/delegate/validate'
                    , [ 'userid' => (string) $entityUser->getUid() ]
                ),
            ],
        ];


        return [
            ListenerDispatch::RESULT_DISPATCH => $r
        ];
    }
}
