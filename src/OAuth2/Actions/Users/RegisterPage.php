<?php
namespace Module\OAuth2\Actions\Users;

use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exRegistration;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\Interfaces\iHttpRequest;


class RegisterPage
    extends aAction
{
    const FLASH_MESSAGE_ID = 'message.register';

    function __invoke(iHttpRequest $request = null)
    {
        # Persist Registration Request:
        if (MethodType::_($request)->isPost()) {
            try
            {
                /** @var $r [ url_validation => (string) ] */
                $r = $this->RegisterRequest($request);

            }
            catch (exRegistration $e) {
                $this->withModule('foundation')->flashMessage(self::FLASH_MESSAGE_ID)
                    ->error($e->getMessage());
                ;
            }
            catch (\Exception $e) {
                $this->withModule('foundation')->flashMessage(self::FLASH_MESSAGE_ID)
                    ->error('سرور در حال حاضر قادر به انجام درخواست شما نیست. لطفا مجدد تلاش کنید.');
                ;
            }

            // redirect to validation page
            return new ResponseRedirect( $r[ListenerDispatch::RESULT_DISPATCH]['url_validation'] );
        }

        # Display Output:

        return [];
    }
}
