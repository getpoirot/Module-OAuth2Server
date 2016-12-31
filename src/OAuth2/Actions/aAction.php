<?php
namespace Module\OAuth2\Actions;

use Module\OAuth2\Actions\Events\EventHeap;
use Module\OAuth2\Actions\Users\Register;
use Module\OAuth2\Actions\Users\RegisterRequest;
use Module\OAuth2\Actions\Users\ValidationGenerator;
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
    /** @var EventHeap */
    protected $events;


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
}
