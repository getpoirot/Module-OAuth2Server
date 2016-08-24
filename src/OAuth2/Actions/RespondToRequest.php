<?php
namespace Module\OAuth2\Actions;

use Module\Foundation\Actions\aAction;

use Poirot\Http\HttpRequest;
use Poirot\Http\HttpResponse;

use Poirot\Http\Psr\ResponseBridgeInPsr;

use Poirot\OAuth2\Server\Grant\Exception\exInvalidRequest;
use Poirot\OAuth2\Server\Grant\GrantAggregateGrants;

class RespondToRequest extends aAction
{
    /**
     * Respond To Access Token Requests
     *
     * @param HttpRequest  $request  Injected service
     * @param HttpResponse $response Injected service
     * 
     * @return HttpResponse
     * @throws exInvalidRequest
     */
    function __invoke(HttpRequest $request = null, HttpResponse $response = null)
    {
        $requestPsr  = \Module\OAuth2\factoryBridgeInPsrServerRequest($request);
        $responsePsr = new ResponseBridgeInPsr($response);
        /** @var GrantAggregateGrants $aggregateGrant */
        $aggregateGrant = $this->GetGrantResponderService();
        if (!$aggregateGrant->canRespondToRequest($requestPsr))
            throw new exInvalidRequest;
        
        $responsePsr    = $aggregateGrant->respond($requestPsr, $responsePsr);

        $responsePsr = \Poirot\Http\parseResponseFromPsr($responsePsr);
        $response    = new HttpResponse($responsePsr);
        return $response;
    }
}
