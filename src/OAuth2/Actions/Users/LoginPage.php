<?php
namespace Module\OAuth2\Actions\Users;

use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Poirot\AuthSystem\Authenticate\Credential\CredentialUserPass;
use Poirot\AuthSystem\Authenticate\Exceptions\exAuthentication;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;


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
            $queryParams = ParseRequestData::_($request)->parseQueryParams();
            $continue    = (isset($queryParams['continue']))
                ? $queryParams['continue']
                : (string) $this->withModule('foundation')->url('main/oauth/me/profile')
            ;

            return new ResponseRedirect($continue);
        }


        # Login Request:
        if (MethodType::_($request)->isPost()) {
            try {
                $this->_login($request);

            } catch (exAuthentication $e) {
                ## Invalid Credential !!!

                $this->withModule('foundation')->flashMessage(self::FLASH_MESSAGE_ID)
                    ->error('نام کاربری و یا کلمه عبور اشتباه است.');
                ;
            }

            // redirect to itself (matchedRoute)
            return new ResponseRedirect( $this->withModule('foundation')->url(null, null, true) );
        }


        # Display Output Login Page:

        return array(
            'message' => 'Please Login!'
        );
    }

    protected function _login(iHttpRequest $request)
    {
        # Validate Sent Data:
        $post = ParseRequestData::_($request)->parseBody();
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

        # Sanitize Data:

        return $post;
    }
}
