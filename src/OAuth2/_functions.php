<?php
namespace Module\OAuth2
{
    use Poirot\Http\HttpMessage\Request\Plugin\PhpServer;
    use Poirot\Http\Interfaces\iHeader;
    use Poirot\Http\Interfaces\iHttpRequest;
    use Poirot\Psr7\HttpServerRequest;

    /**
     * Factory New Psr7-ServerRequest From HttpRequest
     * 
     * @param iHttpRequest $request
     * 
     * @return HttpServerRequest
     */
    function factoryBridgeInPsrServerRequest(iHttpRequest $request)
    {
        $_server = PhpServer::_($request)->getServer();
        $_get    = PhpServer::_($request)->getQuery();
        $_post   = PhpServer::_($request)->getPost();
        $cookie  = PhpServer::_($request)->getCookie();
        
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
            ->withCookieParams(\Poirot\Std\cast($cookie)->toArray())
            ->withQueryParams(\Poirot\Std\cast($_get)->toArray())
            ->withParsedBody(\Poirot\Std\cast($_post)->toArray())
        ;
        
        return $requestPsr;
    }
}
