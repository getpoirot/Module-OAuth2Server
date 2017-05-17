<?php
namespace Module\OAuth2\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\HttpMessage\Response\BuildHttpResponse;
use Poirot\Http\HttpResponse;

use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Http\Interfaces\iHttpResponse;
use Poirot\Http\Psr\ResponseBridgeInPsr;

use Poirot\Http\Psr\ServerRequestBridgeInPsr;
use Poirot\OAuth2\Server\Exception\exOAuthServer;


class RespondToTokenRequest
    extends aAction
{
    protected $response;


    /**
     * RespondToTokenRequest constructor.
     *
     * @param iHttpResponse $httpResponse @IoC /HttpResponse
     * @param iHttpRequest  $httpRequest  @IoC /HttpRequest
     */
    function __construct(iHttpResponse $httpResponse, iHttpRequest $httpRequest)
    {
        parent::__construct($httpRequest);

        $this->response = $httpResponse;
    }


    /**
     * Respond To Access Token Requests
     *
     * @return HttpResponse[]
     * @throws static
     */
    function __invoke()
    {
        $responsePsr = new ResponseBridgeInPsr($this->response);

        try {
            $grant = $this->GrantResponder()
                ->canRespondToRequest( new ServerRequestBridgeInPsr($this->request) );

            if (! $grant )
                throw exOAuthServer::unsupportedGrantType();

            $responsePsr = $grant->respond($responsePsr);

        }
        catch (\Exception $e)
        {
            // Just Rise OAuth Exceptions Error
            $responder = null;

            $exception = $e;
            if (!$e instanceof exOAuthServer) {
                $responder = $this->GrantResponder()
                    ->lastGrantResponder()->newGrantResponse();

                $exception = exOAuthServer::serverError($e->getMessage(), $responder);
            }

            $responsePsr = $exception->buildResponse($responsePsr);
        }


        # Build Response

        $responsePsr = \Poirot\Http\parseResponseFromPsr($responsePsr);
        $response    = new HttpResponse(
            new BuildHttpResponse( BuildHttpResponse::parseWith($responsePsr) )
        );

        return [
            ListenerDispatch::RESULT_DISPATCH => $response
        ];
    }
}
