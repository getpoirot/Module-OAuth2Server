<?php
namespace Module\OAuth2
{
    use Poirot\Http\HttpMessage\Request\Plugin\PhpServer;
    use Poirot\Http\HttpRequest;
    use Poirot\Http\Interfaces\iHeader;
    use Poirot\Psr7\HttpServerRequest;

    /**
     * Factory New Psr7-ServerRequest From HttpRequest
     * 
     * @param HttpRequest $request
     * 
     * @return HttpServerRequest
     */
    function factoryBridgeInPsrServerRequest(HttpRequest $request)
    {
        $_server = PhpServer::_($request)->getServer();

        $headers = array();
        /** @var iHeader $header */
        foreach ($request->headers() as $header)
            $headers[$header->getLabel()] = $header->renderValueLine();

        $requestPsr  = new HttpServerRequest(
            \Poirot\Std\cast($_server)->toArray()
            , PhpServer::_($request)->getFiles()
            , $request->getTarget()
            , $request->getMethod()
            , $request->getBody()
            , $headers
        );

        $requestPsr = $requestPsr
            ->withCookieParams($_COOKIE)
            ->withQueryParams($_GET)
            ->withParsedBody($_POST)
        ;
        
        return $requestPsr;
    }
}
