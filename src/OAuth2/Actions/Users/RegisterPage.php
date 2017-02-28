<?php
namespace Module\OAuth2\Actions\Users;

use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Exception\exRegistration;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Std\Interfaces\Struct\iData;
use Poirot\Std\Struct\DataMean;


class RegisterPage
    extends RegisterRequest
{
    const FLASH_MESSAGE_ID = 'message.register';

    function __invoke(iHttpRequest $request = null)
    {
        # Persist Registration Request:
        if (MethodType::_($request)->isPost())
        {
            $r = (string) $this->withModule('foundation')->url(null, null, true);

            try
            {
                /** @var $r [ url_validation => (string) ] */
                $r = $this->handleRegisterRequest($request, false);
                $r = $r['_link']['next_validate'];
            }
            catch (exIdentifierExists $e) {
                $flash = \Module\Foundation\Actions\IOC::flashMessage(self::FLASH_MESSAGE_ID);
                $flash->error(sprintf('این مشخصه قبلا توسط کاربر دیگری ثبت شده است.'));

                ## tell page that exception catches here; show user account recovery follow
                $uIdentifiers = $e->listIdentifiers();
                /** @var iEntityUserIdentifierObject $uIdentifiers */
                $uIdentifiers = current($uIdentifiers);
                $flash->addObject(
                    (object) array('type' => $uIdentifiers->getType(), 'value' => $uIdentifiers->getValue())
                    , 'catch'
                );
            }
            catch (\Exception $e) {
                \Module\Foundation\Actions\IOC::flashMessage(self::FLASH_MESSAGE_ID)
                    ->error('سرور در حال حاضر قادر به انجام درخواست شما نیست. لطفا مجدد تلاش کنید.');
                ;
            }

            // redirect to validation page
            return new ResponseRedirect( $r );
        }


        # Is Exists Account Identifier When Registering?
        # prepare recovery link
        # 
        if (\Module\Foundation\Actions\IOC::flashMessage(self::FLASH_MESSAGE_ID)->hasObject('catch')) {
            $existsIdentifiers = \Module\Foundation\Actions\IOC::flashMessage(self::FLASH_MESSAGE_ID)->fetchObjects('catch');
            /*
            [meta => NULL
            type String(5) => catch
            value Object => stdClass] // (object) type:$uidn->getType(), value: $uidn->getValue()
            */
            $existsIdentifiers = current($existsIdentifiers); // use once to recognize user
            $u = $this->repoUsers->findOneHasIdentifierWithValue($existsIdentifiers['value']->value);
        }
        
        
        # Build Output:
        $r = [];
        if (isset($u)) {
            $r['recovery_flow'] = [
                'user' => [
                    'uid'      => $u->getUID(),
                    'fullname' => $u->getFullName(),
                    #'avatar'  => $userAvatarUrl
                ],
                '_link' => \Module\Foundation\Actions\IOC::url(
                    'main/oauth/members/signin_challenge'
                    , [ 'uid' => $u->getUID() ]
                ),
            ];
        }
        
        return $r;
    }
}
