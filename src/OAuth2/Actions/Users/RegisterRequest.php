<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;
use Module\OAuth2\Model\Mongo\User;
use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Model\ValidationCodeAuthObject;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;


class RegisterRequest
    extends aAction
{
    function __invoke(iHttpRequest $request = null)
    {
        if ($request === null)
            // Method inside can be used by others
            return $this;


        if (MethodType::_($request)->isPost())
        {
            $user = $this->Register()->persistUser(
                $this->attainUserFromRequest($request)
            );

            // Continue Used to OAuth Registration Follow!!!
            $queryParams = ParseRequestData::_($request)->parseQueryParams();
            $continue    = (isset($queryParams['continue'])) ? $queryParams['continue'] : null;

            $code = $this->Register()->giveUserValidationCode($user, $continue);

            return array(
                ListenerDispatch::RESULT_DISPATCH => array(
                    'url_validation' => (string) $this->withModule('foundation')->url(
                        'main/oauth/validate'
                        , array('validation_code' => $code)
                    ),
                )
            );

        }

        return null;
    }

    function attainUserFromRequest(iHttpRequest $request)
    {
        # Validate Sent Data:
        $post = ParseRequestData::_($request)->parseBody();
        $post = $this->_assertValidData($post);

        # Map Given Data Of API Protocol and Map To Entity Model:
        $identifiers   = [];
        $identifiers[] = ['type' => 'email', 'value' => $post['username'], 'validated' => false];
        if (isset($post['mobile'])) {
            $identifiers[] = [ 'type' => 'mobile', 'value' => [$post['mobile']['country'], $post['mobile']['number']], 'validated' => false ];
        }

        $entity = new \Module\OAuth2\Model\User;
        $entity
            ->setFullName($post['full_name'])
            # ->setIdentifier() // Allow Entity/Persistence Storage Choose Identifier
            ->setUsername($post['username'])
            ->setPassword(md5($post['credential'])) // Add Grant Password
            ->setIdentifiers($identifiers)
        ;

        return $entity;
    }

    /**
     * Assert Validated Registration Post Data
     *
     * Array (
     *   [full_name] => Payam Naderi
     *   [username] => naderi.payam@gmail.com
     *   [credential] => e10adc3949ba59abbe56e057f20f883e
     *   [mobile] => Array (
     *     [country] => +98
     *     [number] => 9355497674
     *   )
     * )
     *
     * @param array $post
     *
     * @return array
     */
    protected function _assertValidData(array $post)
    {
        # Sanitize Data:
        $post['mobile']['number'] = preg_replace('/\s+/', '', $post['mobile']['number']);

        # Validate Data:

        return $post;
    }
}
