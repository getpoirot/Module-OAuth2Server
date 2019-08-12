<?php
namespace Module\OAuth2\Actions;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\HttpMessage\Response\BuildHttpResponse;
use Poirot\Http\HttpResponse;

use Poirot\OAuth2\Server\Exception\exOAuthServer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class RespondToTokenRequest
    extends aAction
{
    protected $response;
    protected $requestPsr;


    /**
     * RespondToTokenRequest constructor.
     *
     * @param ResponseInterface      $HttpResponsePsr @IoC /HttpResponsePsr
     * @param ServerRequestInterface $HttpRequestPsr  @IoC /HttpRequestPsr
     */
    function __construct(ResponseInterface $HttpResponsePsr, ServerRequestInterface $HttpRequestPsr)
    {
        $this->requestPsr = $HttpRequestPsr;
        $this->response   = $HttpResponsePsr;
    }


    /**
     * Respond To Access Token Requests
     *
     * @return HttpResponse[]
     * @throws static
     */
    function __invoke()
    {
        $responsePsr = $this->response;

        try {
            $grant = $this->GrantResponder()
                ->canRespondToRequest( $this->requestPsr );

            if (! $grant )
                throw exOAuthServer::unsupportedGrantType();

            $responsePsr = $grant->respond($responsePsr);

        }
        catch (\Exception $e)
        {
            // Just Rise OAuth Exceptions Error
            $responder = null;

            $exception = $e;
            if (! $e instanceof exOAuthServer ) {
                $responder = $this->GrantResponder()
                    ->lastGrantResponder();

                if ($responder)
                    $responder = $responder->newGrantResponse();

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
