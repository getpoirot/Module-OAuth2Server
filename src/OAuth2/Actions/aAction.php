<?php
namespace Module\OAuth2\Actions;

use Module\Authorization\Actions\AuthenticatorAction;
use Module\OAuth2\Actions\Helper\AttainUsername;
use Module\OAuth2\Actions\Users\Register;
use Module\OAuth2\Actions\User\RegisterRequest;
use Module\OAuth2\Events\EventHeap;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Model\ValidationEntity;
use Module\OAuth2\Module;
use Poirot\AuthSystem\Authenticate\Authenticator;
use Poirot\AuthSystem\Authenticate\Interfaces\iAuthenticator;
use Poirot\Events\Interfaces\iEvent;
use Poirot\Events\Interfaces\Respec\iEventProvider;
use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;
use Psr\Http\Message\ResponseInterface;


/**
 * # Registered Module Action:
 *
 * @see                        AttainUsername
 * @method string              AttainUsername(iOAuthUser $user)
 * ..........................................................................................................
 * @see                        MadeUserIdentifierValidationState
 * @method ValidationEntity|null MadeUserIdentifierValidationState(iOAuthUser $user, $continue = null)
 * ..........................................................................................................
 * @see                        GenIdentifierAuthCode
 * @method string              GenIdentifierAuthCode(iUserIdentifierObject $ident = null)
 * ...........................................................................................................
 * @method Register            Register()
 * ...........................................................................................................
 *
 * @method ResponseInterface   RespondToRequest(HttpRequest $request, HttpResponse $response)
 * @method iOAuthUser          RetrieveAuthenticatedUser()
 * @method RegisterRequest     RegisterRequest(iHttpRequest $request = null)
 *
 */
abstract class aAction
    extends \Module\Foundation\Actions\aAction
    implements iEventProvider
{
    /** @var iHttpRequest */
    protected $request;
    /** @var EventHeap */
    protected $events;

    protected $_authenticator;


    /**
     * aAction constructor.
     *
     * @param iHttpRequest $request @IoC /
     */
    function __construct(iHttpRequest $request)
    {
        $this->request = $request;
    }


    /**
     * Get Events
     *
     * @return iEvent
     */
    function event()
    {
        if (!$this->events)
            $this->events = new EventHeap;

        return $this->events;
    }


    // ..

    /**
     * Get OAuth Authenticator
     * @return iAuthenticator|Authenticator
     */
    function _authenticator()
    {
        if ($this->_authenticator)
            return $this->_authenticator;

        /** @var AuthenticatorAction $authenticator */
        $authenticator = $this->withModule('authorization')->Authenticator();
        $authenticator = $authenticator->authenticator(Module::AUTHENTICATOR);
        return $this->_authenticator = $authenticator;
    }
}
