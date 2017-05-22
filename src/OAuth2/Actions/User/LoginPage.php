<?php
namespace Module\OAuth2\Actions\User;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\HttpFoundation\Response\ResponseRedirect;
use Poirot\Http\HttpMessage\Request\Plugin;
use Module\OAuth2\Actions\aAction;
use Poirot\AuthSystem\Authenticate\Credential\CredentialUserPass;
use Poirot\AuthSystem\Authenticate\Exceptions\exAuthentication;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\Std\Exceptions\exUnexpectedValue;


class LoginPage
    extends aAction
{
    const FLASH_MESSAGE_ID = 'message.login';


    function __invoke()
    {
        $request = $this->request;

        # Check Current User:
        if ( $this->_authenticator()->hasAuthenticated() ) {
            // User Is Logged In; Continue Redirection
            $queryParams = Plugin\ParseRequestData::_($request)->parseQueryParams();
            $continue    = (isset($queryParams['continue']))
                ? $queryParams['continue']
                : (string) \Module\HttpFoundation\Actions::url('main/oauth/me/profile')
            ;

            return [
                ListenerDispatch::RESULT_DISPATCH => new ResponseRedirect($continue)
            ];
        }


        # Login Request:
        if ( Plugin\MethodType::_($request)->isPost() ) {
            try {
                $this->_login($request);
            }
            catch (exUnexpectedValue $e) {
                \Module\HttpFoundation\Actions::flashMessage(self::FLASH_MESSAGE_ID)
                    ->error( $e->getMessage() );
                ;
            }
            catch (exAuthentication $e) {
                ## Invalid Credential !!!
                \Module\HttpFoundation\Actions::flashMessage(self::FLASH_MESSAGE_ID)
                    ->error('نام کاربری و یا کلمه عبور اشتباه است.');
                ;
            }
            catch (\Exception $e) {
                // TODO Log Critical Error

                \Module\HttpFoundation\Actions::flashMessage(self::FLASH_MESSAGE_ID)
                    ->error('سیستم در حال حاضر قادر به پاسخگویی نیست.');
                ;
            }


            // redirect to itself (matchedRoute)
            return new ResponseRedirect( (string) \Module\HttpFoundation\Actions::url(null, null, true) );
        }


        # Display Output Login Page:

        return array(
            'message' => 'Please Login!'
        );
    }

    /**
     * Handle Login Request
     *
     * @param iHttpRequest $request
     */
    protected function _login(iHttpRequest $request)
    {
        # Validate Sent Data:
        $post = Plugin\ParseRequestData::_($request)->parseBody();
        $post = $this->_assertValidData($post);

        $identifier = $this->_authenticator()->authenticate(
            __(new CredentialUserPass())
                ->setUsername($post['username'])
                ->setPassword($post['password'])
        );

        $identifier->signIn();
    }


    // ..

    /**
     * Assert Validated Registration Post Data
     *
     * Array (
     *   [username] => naderi.payam@gmail.com
     *   [password] => '******'
     * )
     *
     * @param array $post
     *
     * @return array
     */
    protected function _assertValidData(array $post)
    {
        # Validate Data:
        if ( !isset($post['username']) || !isset($post['password']) )
            throw new exUnexpectedValue('پارامتر های ورودی را کامل وارد نکرده اید.');

        # Sanitize Data:
        $post['username'] = trim($post['username']);

        return $post;
    }
}
