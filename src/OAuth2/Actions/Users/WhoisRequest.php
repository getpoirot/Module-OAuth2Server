<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Exception\exRegistration;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Model\UserIdentifierObject;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\MethodType;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\Interfaces\iHttpRequest;


class WhoisRequest
    extends aAction
{
    function __invoke(iHttpRequest $request = null)
    {
        if ($request === null)
            // Method inside can be used by others
            return $this;

        return array(
            ListenerDispatch::RESULT_DISPATCH => $this->handleRequest($request),
        );
    }

    /**
     * Handle Register Post Request
     *
     * @param iHttpRequest $request
     *
     * @return array|null
     */
    function handleRequest(iHttpRequest $request)
    {
        # Validate Sent Data:
        $post = ParseRequestData::_($request)->parse();
        $post = $this->_assertValidData($post);

        # Get Identifier Match
        $identifier = new UserIdentifierObject;
        $identifier->setType(key($post));
        $identifier->setValue(current($post));

        /** @var Users $repoUsers */
        $repoUsers = $this->IoC()->get('services/repository/Users');
        /** @var iEntityUser $u */
        $u = $repoUsers->findOneMatchByIdentifiers([$identifier], true);

        if (!$u)
            // Resource not Found
            throw new exRouteNotMatch;

        # make response data

        return array(
            'uid' => $u->getUID(),
            'profile' => [
                'fullname' => $u->getFullName(),
                'username' => $u->getUsername(),
            ],
        );
    }


    // ..

    /**
     * Assert Validated Registration Post Data
     *
     * Array (
     *   [username] => naderi.payam@gmail.com
     *   or ----------------------
     *   [mobile] => Array (
     *     [country] => +98
     *     [number] => 9355497674
     *   )
     *   -------------------------
     *   ...
     * )
     *
     * @param array $post
     *
     * @return array
     */
    protected function _assertValidData(array $post)
    {
        # Sanitize Data:

        # Validate Data:
        if (count($post) > 1)
            throw new \InvalidArgumentException('Too Many Parameters');

        return $post;
    }
}
