<?php
namespace Module\OAuth2\Actions;


use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Resource\Validation\AuthorizeByInternalServer;

class AssertAuthToken extends aAction
{
    /**
     * Assert Authorization Token From Request
     *
     * @param iHttpRequest $request
     *
     * @return array('token' => iEntityAccessToken)
     */
    function __invoke($request = null)
    {
        $requestPsr       = \Module\OAuth2\factoryBridgeInPsrServerRequest($request);

        // pass token as collector result chain to other action
        return array('token' => $this->_validator()->hasValidated($requestPsr));
    }

    protected function _validator()
    {
        $repoAccessTokens = $this->IoC()->get('services/repository/AccessTokens');
        $validator        = new AuthorizeByInternalServer($repoAccessTokens);

        return $validator;
    }
}
