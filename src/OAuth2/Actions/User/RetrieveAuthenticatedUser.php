<?php
namespace Module\OAuth2\Actions\Users;

use Module\Authorization\Module\AuthenticatorAction;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Model\UserEntity;
use Module\OAuth2\Module;


class RetrieveAuthenticatedUser
    extends aAction
{
    /**
     * Retrieve Authenticated User
     *
     */
    function __invoke()
    {
        /** @var AuthenticatorAction $authenticator */
        $authenticator = \Module\Authorization\Actions\IOC::Authenticator();
        if (!$identifier = $authenticator->authenticator(Module::AUTHENTICATOR)->hasAuthenticated())
            return false;


        $identity = $identifier->withIdentity();

        $user = new UserEntity($identity);
        if ($user->getUID() === null)
            throw new \Exception(sprintf(
                'Identifier (%s) With Identity (%s) not fulfilled OAuth Entity User on "identifier" property.'
                , get_class($identifier), get_class($identity)
            ));

        return $user;
    }
}
