<?php
namespace Module\OAuth2\Actions;

use Module\Authorization\Actions\AuthenticatorAction;
use Module\OAuth2\Actions\Events\EventHeap;
use Module\OAuth2\Actions\Users\Register;
use Module\OAuth2\Actions\Users\RegisterRequest;
use Module\OAuth2\Actions\Users\ValidationGenerator;
use Module\OAuth2\Module;
use Poirot\AuthSystem\Authenticate\Authenticator;
use Poirot\AuthSystem\Authenticate\Interfaces\iAuthenticator;
use Poirot\Events\Interfaces\iEvent;
use Poirot\Events\Interfaces\Respec\iEventProvider;
use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;
use Psr\Http\Message\ResponseInterface;


/**
 * # Registered Module Action:
 *
 * @method ResponseInterface   RespondToRequest(HttpRequest $request, HttpResponse $response)
 * @method iEntityUser         RetrieveAuthenticatedUser()
 * @method ValidationGenerator ValidationGenerator($uid = null, array $identifiers = null, $continue = null)
 * @method Register            Register()
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
        $authenticator = \Module\Authorization\Actions\IOC::Authenticator();
        $authenticator = $authenticator->authenticator(Module::AUTHENTICATOR);
        return $this->_authenticator = $authenticator;
    }
}
