<?php
namespace Module\OAuth2\Actions;

use Module\OAuth2\Actions\Users\ValidationGenerator;
use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;
use Psr\Http\Message\ResponseInterface;

/**
 * # Registered Module Action:
 *
 * @method ResponseInterface   RespondToRequest(HttpRequest $request, HttpResponse $response)
 * @method iEntityUser         RetrieveAuthenticatedUser()
 * @method ValidationGenerator ValidationGenerator($uid = null, array $identifiers = null, $continue = null)
 *
 */
abstract class aAction
    extends \Module\Foundation\Actions\aAction
{

}
