<?php
namespace Module\OAuth2\Actions;

use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;
use Psr\Http\Message\ResponseInterface;

/**
 * # Registered Module Action:
 *
 * @method ResponseInterface RespondToRequest(HttpRequest $request, HttpResponse $response)
 * @method iEntityUser       RetrieveAuthenticatedUser()
 *
 * @property RespondToRequest          RespondToRequest
 * @property RetrieveAuthenticatedUser RetrieveAuthenticatedUser
 */
abstract class aAction extends \Module\Foundation\Actions\aAction
{ }
