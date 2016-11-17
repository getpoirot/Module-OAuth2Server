<?php
namespace Module\OAuth2\Actions;

use Module\Foundation\Actions\aAction;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\Interfaces\iHttpRequest;


class RegisterController extends aAction
{
    function __invoke(iHttpRequest $request = null)
    {
        if (MethodType::_($request)->isPost()) {
            try {
                $this->register($request);

                // redirect to itself (matchedRoute)
                return new ResponseRedirect( $this->withModule('foundation')->url() );
            } catch (\Exception $e) {
                // TODO implement flash messages
                die($e->getMessage());
            }
        }

        return []; // display template output
    }
}
