<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exIdentifierExists;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Model\UserIdentifierObject;
use Module\OAuth2\Model\ValidationCodeAuthObject;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class ChangeIdentity
    extends aAction
{
    /**
     * @param string $uid
     * @param array $identifiers
     *
     * @return array
     * @throws exIdentifierExists
     */
    function __invoke($uid = null, $identifiers = null)
    {
        /** @var Users $repoUsers */
        $repoUsers = $this->IoC()->get('services/repository/Users');


        # Check Identifier Uniqueness:
        /** @var iEntityUserIdentifierObject $ident */
        if ($repoUsers->isIdentifiersRegistered($identifiers))
            throw new exIdentifierExists('Identifier Is Given To Another User.', 400);

        /** @var iEntityUserIdentifierObject $ident */
        $authCodes = [];
        foreach ($identifiers as $ident)
            $authCodes[] = ValidationCodeAuthObject::newByIdentifier($ident);

        $code = $this->ValidationGenerator($uid, $authCodes);
        return array(
            ListenerDispatch::RESULT_DISPATCH => array(
                'url_validation' => (string) $this->withModule('foundation')->url(
                    'main/oauth/api/me/identifiers/confirm'
                    , array('validation_code' => $code)
                ),
            )
        );
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
        $identifiers = [];
        foreach ($post as $k => $v)
            $identifiers[] = new UserIdentifierObject(['type'=>$k, 'value'=>$v, 'validated'=>false]);

        return ['identifiers' => $identifiers];
    }
}
