<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exPasswordNotMatch;
use Module\OAuth2\Model\Mongo\Users;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class ChangePassword
    extends aAction
{
    /**
     * Change Password Grant Credential By User ID
     *
     * @param string $uid      User uid
     * @param string $newpass
     * @param string $currpass
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($uid = null, $newpass = null, $currpass = null)
    {
        /** @var Users $repoUsers */
        $repoUsers = $this->moduleServices()->get('services/repository/Users');

        // Current password must match
        $u = $repoUsers->findOneByUID($uid);
        if ($repoUsers->makeCredentialHash($currpass) !== $u->getPassword())
            throw new exPasswordNotMatch('Current Password Does not match!');

        $r = $repoUsers->updateGrantTypeValue($uid, 'password', $newpass);

        return [
            // TODO dispatch result from chained route closure!!
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
    static function functorGetParsedRequestData()
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

    static function functorGetParsedUIDFromToken()
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
        if (!isset($post['newpass']) || !isset($post['currpass']))
            throw new \InvalidArgumentException('Arguments "newpass" & "currpass" is required.');

        # Sanitize Data:

        return $post;
    }
}
