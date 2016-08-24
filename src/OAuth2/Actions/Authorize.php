<?php
namespace Module\OAuth2\Actions;

use Module\Foundation\Actions\aAction;

use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;

class Authorize extends aAction
{
    function __invoke(HttpRequest $request = null, HttpResponse $response = null)
    {
        return $this->RespondToRequest($request, $response);
    }
}
