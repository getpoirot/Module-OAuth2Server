<?php
namespace Module\OAuth2\Actions;

use Module\Foundation\Actions\aAction;

use Module\OAuth2\Interfaces\Server\Repository\iRepoUsersApprovedClients;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\PhpServer;
use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;
use Poirot\Http\Psr\ResponseBridgeInPsr;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityClient;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser;
use Poirot\OAuth2\Server\Exception\exOAuthServer;
use Poirot\OAuth2\Server\Grant\aGrant;
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
class Authorize extends aAction
{
    function __invoke(HttpRequest $request = null, HttpResponse $response = null)
    {
        $respondToRequestAction = $this->RespondToRequest;
        $aggregateGrant         = $respondToRequestAction->grantResponder();

        $requestPsr  = \Module\OAuth2\factoryBridgeInPsrServerRequest($request);
        /** @var aGrant $grant */
        if (!$grant = $aggregateGrant->canRespondToRequest($requestPsr))
            throw exOAuthServer::unsupportedGrantType();


        $_post = PhpServer::_($request)->getPost();

        /** @var iEntityClient $client */
        $client = $grant->assertClient(false);
        list($scopeRequested, $scopes) = $grant->assertScopes($client->getScope());


        ##

        // check whether to display approve page or not?
        if (!$approveNotRequire = $client->isResidentClient()) {
            /** @var iRepoUsersApprovedClients $RepoApprovedClients */
            $RepoApprovedClients = $this->ModuleServices()->get('repository/users.approved_clients');
            $User = $this->RetrieveAuthenticatedUser();

            //// also maybe client approve the client in the past
            $approveNotRequire = $RepoApprovedClients->isUserApprovedClient($User, $client);
        }

        if (false == $approveNotRequire)
        {
            if (MethodType::_($request)->isPost() && $_post->get('deny_access', null) !== null) {
                // Get Deny Result Back To The Client
                $responsePsr = new ResponseBridgeInPsr($response);
                $exception   = exOAuthServer::accessDenied($grant->newGrantResponse());
                $responsePsr = $exception->buildResponse($responsePsr);
                $responsePsr = \Poirot\Http\parseResponseFromPsr($responsePsr);
                $response    = new HttpResponse($responsePsr);
                return $response;

            } elseif (MethodType::_($request)->isPost() && $_post->get('allow_access', null) !== null) {
                // Allow Access The Client
                $RepoApprovedClients = $this->ModuleServices()->get('repository/users.approved_clients');
                $User = $this->RetrieveAuthenticatedUser();
                $RepoApprovedClients->approveClient($User, $client);
            } else {
                ## display approve page
                return array(
                    'client' => array(
                        'name'        => $client->getName(),
                        'description' => $client->getDescription(),
                        'image_url'   => $client->getImage(),
                    ),
                    'scopes' => $scopes,
                );
            }
        }

        // Client is resident or approved by user
        return $respondToRequestAction($request, $response);
    }
}
