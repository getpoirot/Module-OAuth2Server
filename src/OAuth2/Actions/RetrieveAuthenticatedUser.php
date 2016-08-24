<?php
namespace Module\OAuth2\Actions;

use Module\Authorization\Module\AuthenticatorFacade;
use Module\Foundation\Actions\aAction;

use Module\OAuth2\Model\User;
use Module\OAuth2\Module;
use Poirot\OAuth2\Server\Grant\Exception\exInvalidRequest;


class RetrieveAuthenticatedUser extends aAction
{
    /**
     * Retrieve Authenticated User
     *
     * @throws exInvalidRequest
     */
    function __invoke()
    {
        /** @var AuthenticatorFacade $authenticator */
        $authenticator = $this->services()->get('/module/authorization');
        if (!$identifier = $authenticator->authenticator(Module::AUTHENTICATOR)->hasAuthenticated())
            return false;

        $identity = $identifier->withIdentity();

        return new User($identity);
    }
}
