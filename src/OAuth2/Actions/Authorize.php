<?php
namespace Module\OAuth2\Actions;

use Module\Foundation\Actions\aAction;

use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;

/**
 * # Registered Module Action:
 * @method RespondToRequest RespondToRequest(HttpRequest $request, HttpResponse $response)
 */
class Authorize extends aAction
{
    function __invoke(HttpRequest $request = null, HttpResponse $response = null)
    {
        return $this->RespondToRequest($request, $response);
    }
}
