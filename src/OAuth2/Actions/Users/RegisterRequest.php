<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\Interfaces\iHttpRequest;


class RegisterRequest
    extends aAction
{
    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * ValidatePage constructor.
     * @param iRepoUsers           $users           @IoC /module/oauth2/services/repository/
     */
    function __construct(iRepoUsers $users)
    {
        $this->repoUsers = $users;
    }


    function __invoke(iHttpRequest $request = null)
    {
        if ($request === null)
            // Method inside can be used by others
            return $this;

        return array(
            ListenerDispatch::RESULT_DISPATCH => $this->handleRegisterRequest($request, true),
        );
    }

    /**
     * Handle Register Post Request
     *
     * @param iHttpRequest $request
     *
     * @return array|null
     */
    function handleRegisterRequest(iHttpRequest $request)
    {
        $user = $this->Register()->persistUser($userEntity);
        
        /** @var iUserIdentifierObject $ident */
        $validate = []; $ids = [];
        foreach ($user->getIdentifiers() as $ident)
        {
            $ids[$ident->getType()] = $ident->getValue();

            if ($ident->isValidated())
                continue;

            $validate[] = $ident->getType();
        }

        $validationCode = $this->Register()->giveUserValidationCode($user, $continue);


        # make response data

        $r = [
            'uid'           => $user->getUID(),
            'identifiers'   => $ids,
            'validate'      => $validate,
        ];

        (!$validationCode) ?: $r += [
            '_link' => [
                'next_validate' => (string) $this->withModule('foundation')->url(
                    'main/oauth/members/validate'
                    , ['validation_code' => $validationCode]
                ),
            ],
        ];

        return $r;
    }
}
