<?php
namespace Module\OAuth2\Actions;

use Module\Foundation\Actions\aAction;

use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;

use Poirot\Http\Psr\ResponseBridgeInPsr;

use Poirot\OAuth2\Server\Exception\exOAuthServer;
use Poirot\OAuth2\Server\Grant\GrantAggregateGrants;

/**
 * @property GrantAggregateGrants GetGrantResponderService
 */
class RespondToRequest extends aAction
{
    /**
     * Respond To Access Token Requests
     *
     * @param HttpRequest $request Injected service
     * @param HttpResponse $response Injected service
     * @return HttpResponse
     *
     * @throws static
     */
    function __invoke(HttpRequest $request = null, HttpResponse $response = null)
    {
        $responsePsr = new ResponseBridgeInPsr($response);

        $requestPsr  = \Module\OAuth2\factoryBridgeInPsrServerRequest($request);
        $aggregateGrant = $this->grantResponder();

        try {
            if (!$grant = $aggregateGrant->canRespondToRequest($requestPsr))
                throw exOAuthServer::unsupportedGrantType();

            $responsePsr    = $grant->respond($responsePsr);

        } catch (\Exception $e)
        {
            // Just Rise OAuth Exceptions Error
            $responder = null;

            $exception = $e;
            if (!$e instanceof exOAuthServer) {
                $responder = $aggregateGrant->lastGrantResponder()->newGrantResponse();
                $exception = exOAuthServer::serverError($e->getMessage(), $responder);
            }

            $responsePsr = $exception->buildResponse($responsePsr);
        }

        $responsePsr = \Poirot\Http\parseResponseFromPsr($responsePsr);
        $response    = new HttpResponse($responsePsr);
        return $response;
    }

    /**
     * @return GrantAggregateGrants
     */
    function grantResponder()
    {
        return $this->GetGrantResponderService;
    }
}
