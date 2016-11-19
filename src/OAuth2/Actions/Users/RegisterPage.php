<?php
namespace Module\OAuth2\Actions\Users;

use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\Interfaces\iHttpRequest;


class RegisterPage extends aAction
{
    function __invoke(iHttpRequest $request = null)
    {
        # Persist Registration Request:
        if (MethodType::_($request)->isPost()) {
            try
            {
                $this->register($request);

                // redirect to itself (matchedRoute)
                return new ResponseRedirect( $this->withModule('foundation')->url() );
            } catch (\Exception $e) {
                // TODO implement flash messages
                die($e->getMessage());
            }
        }

        # Display Output:

        return [];
    }
}
