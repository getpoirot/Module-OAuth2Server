<?php
namespace Module\OAuth2
{

    use Poirot\Http\Interfaces\iHttpRequest;
    use Poirot\Http\Psr\ServerRequestBridgeInPsr;
    use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
    use Poirot\OAuth2\Resource\Validation\AuthorizeByInternalServer;

    /**
     * Assert Authorization Token From Request
     *
     * @param iHttpRequest $request
     *
     * @return iEntityAccessToken
     */
    function assertAuthToken(iHttpRequest $request)
    {
        $requestPsr = new ServerRequestBridgeInPsr($request);

        $repoAccessTokens = \Module\OAuth2\Services\Repository\IOC::AccessTokens();
        $validator        = new AuthorizeByInternalServer($repoAccessTokens);

        // pass token as collector result chain to other action
        return $validator->hasValidated($requestPsr);
    }
}
