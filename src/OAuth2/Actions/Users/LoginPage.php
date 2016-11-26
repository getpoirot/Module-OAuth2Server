<?php
namespace Module\OAuth2\Actions\Users;

use Module\Authorization\Module\AuthenticatorFacade;
use Module\Foundation\HttpSapi\Response\ResponseRedirect;
use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Module;
use Poirot\AuthSystem\Authenticate\Authenticator;
use Poirot\AuthSystem\Authenticate\Credential\CredentialUserPass;
use Poirot\AuthSystem\Authenticate\Exceptions\exAuthentication;
use Poirot\AuthSystem\Authenticate\Interfaces\iAuthenticator;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;

class LoginPage extends aAction
{
    function __invoke(iHttpRequest $request = null)
    {
        if (!$request instanceof iHttpRequest)
            throw new \InvalidArgumentException(sprintf(
                'Request Http Must Instance of iHttpRequest; given: (%s).'
                , \Poirot\Std\flatten($request)
            ));


        # Check Current User:
        if ($this->_getAuthenticator()->hasAuthenticated()) {
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

                // TODO set flash message
                // ...
                print_r($e->getMessage());die;
            }

            // redirect to itself (matchedRoute)
            return new ResponseRedirect( $this->withModule('foundation')->url(null, null, true) );
        }


        # Display Output Login Page:

        return array(

        );
    }

    protected function _login(iHttpRequest $request)
    {
        # Validate Sent Data:
        $post = ParseRequestData::_($request)->parseBody();
        $post = $this->_assertValidData($post);

        $identifier = $this->_getAuthenticator()->authenticate(
            __(new CredentialUserPass())
                ->setUsername($post['username'])
                ->setPassword($post['password'])
        );

        $identifier->signIn();
    }


    // ..

    /**
     * Get OAuth Authenticator
     * @return iAuthenticator|Authenticator
     */
    function _getAuthenticator()
    {
        /** @var AuthenticatorFacade $authenticator */
        $authenticator = $this->withModule('authorization')->Facade();
        $authenticator = $authenticator->authenticator(Module::AUTHENTICATOR);
        return $authenticator;
    }

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
