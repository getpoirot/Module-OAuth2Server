<?php
namespace Module\OAuth2\Actions\Users;

use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
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
                /** @var $r [ url_validation => (string) ] */
                $r = $this->register($request);

            } catch (\Exception $e) {
                // TODO implement flash messages
                die($e->getMessage());
            }

            // redirect to validation page
            return new ResponseRedirect( $r[ListenerDispatch::RESULT_DISPATCH]['url_validation'] );
        }

        # Display Output:

        return [];
    }
}
