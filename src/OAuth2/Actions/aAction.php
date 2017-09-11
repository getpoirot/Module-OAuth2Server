<?php
namespace Module\OAuth2\Actions;

use Module\Authorization\Actions\AuthenticatorAction;
use Module\OAuth2\Actions\Helper\AttainUsername;
use Module\OAuth2\Actions\User\Register;
use Module\OAuth2\Actions\Validation\Validation;
use Module\OAuth2\Events\EventHeapOfOAuth;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Module;
use Poirot\AuthSystem\Authenticate\Authenticator;
use Poirot\AuthSystem\Authenticate\Interfaces\iAuthenticator;
use Poirot\Events\Event\BuildEvent;
use Poirot\Events\Event\MeeterIoc;
use Poirot\Events\Interfaces\Respec\iEventProvider;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Http\Interfaces\iHttpResponse;
use Poirot\OAuth2\Server\Grant\GrantAggregateGrants;
use Psr\Http\Message\ResponseInterface;


/**
 * # Registered Module Action:
 *
 * @see                        AttainUsername
 * @method string              AttainUsername(iOAuthUser $user)
 * ..........................................................................................................
 * @see                        GenIdentifierAuthCode
 * @method string              GenIdentifierAuthCode(iUserIdentifierObject $ident = null)
 * ...........................................................................................................
 * @see                        Register
 * @method Register            Register()
 * ...........................................................................................................
 * @see                        Validation
 * @method Validation          Validation()
 * ...........................................................................................................
 * @see                         GrantResponder
 * @method GrantAggregateGrants GrantResponder()
 * ...........................................................................................................
 * @method ResponseInterface   RespondToRequest(iHttpRequest $request, iHttpResponse $response)
 * @method iOAuthUser          RetrieveAuthenticatedUser()
 *
 */
abstract class aAction
    extends \Module\Foundation\Actions\aAction
    implements iEventProvider
{
    const CONF = 'events';


    /** @var iHttpRequest */
    protected $request;
    /** @var EventHeapOfOAuth */
    protected $events;

    protected $_authenticator;


    /**
     * aAction constructor.
     *
     * @param iHttpRequest $httpRequest @IoC /HttpRequest
     */
    function __construct(iHttpRequest $httpRequest)
    {
        $this->request = $httpRequest;
    }


    /**
     * Get Events
     *
     * @return EventHeapOfOAuth
     */
    function event()
    {
        if (! $this->events ) {
            // Build Events From Merged Config
            $conf   = $this->sapi()->config()->get( \Module\OAuth2\Module::CONF_KEY );
            $conf   = $conf[self::CONF];

            $events = new EventHeapOfOAuth;
            $builds = new BuildEvent([ 'meeter' => new MeeterIoc, 'events' => $conf ]);
            $builds->build($events);

            $this->events = $events;
        }

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
        $authenticator = $authenticator->authenticator(Module::REALM);
        return $this->_authenticator = $authenticator;
    }
}
