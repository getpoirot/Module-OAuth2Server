<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Model\Mongo\Users;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class ChangePassword
    extends aAction
{
    /**
     * @param string             $uid
     * @param string             $credential
     * @return array
     */
    function __invoke($uid = null, $credential = null)
    {
        /** @var Users $repoUsers */
        $repoUsers = $this->IoC()->get('services/repository/Users');
        $r = $repoUsers->updateGrantTypeValue($uid, 'password', $credential);

        return [
            ListenerDispatch::RESULT_DISPATCH => ($r) ? 'changed' : 'unchanged',
        ];
    }


    // Statical Route Chain Helpers:

    /**
     * Used With Chained Actions To Extract Data From Request
     *
     * note: currently with dispatcher listener we cant retrieve both
     *       services and chained result together
     *
     * @return callable
     */
    static function getParsedRequestDataClosure()
    {
        /**
         * @param iHttpRequest $request
         * @return array
         */
        return function (iHttpRequest $request = null) {
            # Validate Sent Data:
            $post = ParseRequestData::_($request)->parse();
            $post = __(new self)->_assertValidData($post);

            return $post;
        };
    }

    static function getParsedUIDFromTokenClosure()
    {
        /**
         * note: currently with dispatcher listener we cant retrieve both
         *       services and chained result together
         *
         * @param iEntityAccessToken $token
         * @return array
         */
        return function ($token = null) {
            // Retrieve from token
            $uid = $token->getOwnerIdentifier();
            return ['uid' => $uid];
        };
    }


    // ..

    /**
     * Assert Validated Change Password Post Data
     *
     * Array (
     *   [credential] => e10adc3949ba59abbe56e057f20f883e
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
