<?php
namespace Module\OAuth2\Actions\Users;

use Module\Authorization\Module\AuthenticatorFacade;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Model\User;
use Module\OAuth2\Module;


class RetrieveAuthenticatedUser extends aAction
{
    /**
     * Retrieve Authenticated User
     *
     */
    function __invoke()
    {
        // TODO better access in facade Module
        /** @var AuthenticatorFacade $authenticator */
        $authenticator = $this->withModule('authorization')->Facade();
        if (!$identifier = $authenticator->authenticator(Module::AUTHENTICATOR)->hasAuthenticated())
            return false;

        $identity = $identifier->withIdentity();

        $user = new User($identity);
        if ($user->getIdentifier() === null)
            throw new \Exception(sprintf(
                'Identifier (%s) With Identity (%s) not fulfilled OAuth Entity User on "identifier" property.'
                , get_class($identifier), get_class($identity)
            ));

        return $user;
    }
}
