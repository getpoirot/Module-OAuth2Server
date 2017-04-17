<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\UserIdentifierObject;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;


class WhoisRequest
    extends aAction
{
    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * Constructor.
     * @param iRepoUsers $users @IoC /module/oauth2/services/repository/
     */
    function __construct(iRepoUsers $users)
    {
        $this->repoUsers = $users;
    }

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

        $repoUsers = $this->repoUsers;
        /** @var iOAuthUser $u */
        $u = $repoUsers->findOneMatchByIdentifiers([$identifier]);


        # make response data

        if (!$u) {
            // Indicate no Content
            $response = new HttpResponse;
            $response->setStatusCode(204);
            return $response;
        }

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
