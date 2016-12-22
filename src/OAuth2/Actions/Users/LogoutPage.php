<?php
namespace Module\OAuth2\Actions\Users;

use Module\Authorization\Module\AuthenticatorFacade;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Model\Mongo\Clients;
use Module\OAuth2\Module;
use Poirot\AuthSystem\Authenticate\Authenticator;
use Poirot\AuthSystem\Authenticate\Interfaces\iAuthenticator;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;


class LogoutPage extends aAction
{
    function __invoke(iHttpRequest $request = null)
    {
        if ($this->_getAuthenticator()->hasAuthenticated())
            $this->_getAuthenticator()->identifier()->signOut();

        # Request Client For Redirect URI
        $query = ParseRequestData::_($request)->parseQueryParams();
        if (isset($query['client_id']) && isset($query['redirect_uri'])) {
            /** @var Clients $clientModel */
            $clientModel = $this->IoC()->get('services/repository/Clients');

            $clientId    = $query['client_id'];
            $redirectUri = rtrim($query['redirect_uri'], '/');
            $client      = $clientModel->findByIdentifier($clientId);

            $match = false;
            foreach ($client->getRedirectUri() as $registeredRedirect) {
                $registeredRedirect = rtrim($registeredRedirect, '/');
                if ($redirectUri == $registeredRedirect) {
                    $match = true;
                    break;
                }
            }

            if ( $match )
                return new ResponseRedirect( $redirectUri );
        }


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
