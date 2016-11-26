<?php
namespace Module\OAuth2\Actions\Users;

use Module\Authorization\Module\AuthenticatorFacade;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Module;
use Poirot\AuthSystem\Authenticate\Authenticator;
use Poirot\AuthSystem\Authenticate\Interfaces\iAuthenticator;


class LogoutPage extends aAction
{
    function __invoke()
    {
        if ($this->_getAuthenticator()->hasAuthenticated())
            $this->_getAuthenticator()->identifier()->signOut();

        return new ResponseRedirect( $this->withModule('foundation')->url('main/home') );
    }

    /**
     * Get OAuth Authenticator
     * @return iAuthenticator|Authenticator
     */
    function _getAuthenticator()
    {
        /** @var AuthenticatorFacade $authenticator */
        $authenticator = $this->withModule('authorization')->Facade();
        $authenticator = $authenticator->authenticator(Module::AUTHENTICATOR);
        return $authenticator;
    }
}
