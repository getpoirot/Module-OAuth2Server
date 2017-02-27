<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Model\UserIdentifierObject;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;


class isExistsUserWithIdentifier
    extends aAction
{
    /**
     * Check that user with given identity exist?
     *
     * @param []iEntityUserIdentifierObject $identifier
     *
     * @return array [ name => (bool) ]
     */
    function __invoke(array $identifiers = null)
    {
        /** @var Users $repoUsers */
        $repoUsers = $this->IoC()->get('services/repository/Users');

        /** @var iEntityUserIdentifierObject $ident */
        $return = [];
        foreach ($identifiers as $ident) {
            $r = $repoUsers->hasAnyIdentifiersRegistered([$ident]);
            $return[$ident->getType()] = $r;
        }

        return [
            // TODO dispatch result from chained route closure!!
            ListenerDispatch::RESULT_DISPATCH => $return,
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
            return ['identifiers' => $post];
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
        foreach ($post as $i => $v) {
            $identifier = new UserIdentifierObject;
            $identifier->setType($i);
            $identifier->setValue($v);

            $post[$i] = $identifier;
        }

        return $post;
    }
}
