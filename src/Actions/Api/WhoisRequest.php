<?php
namespace Module\OAuth2\Actions\Api;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\OAuth2\Actions\aApiAction;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Poirot\Http\HttpMessage\Request\Plugin\ParseRequestData;
use Poirot\Http\HttpResponse;
use Poirot\Http\Interfaces\iHttpRequest;


class WhoisRequest
    extends aApiAction
{
    protected $tokenMustHaveOwner  = false;
    protected $tokenMustHaveScopes = array(

    );

    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * @param iRepoUsers           $users       @IoC /module/oauth2/services/repository/Users
     * @param iHttpRequest         $httpRequest @IoC /HttpRequest
     */
    function __construct(iRepoUsers $users, iHttpRequest $httpRequest)
    {
        $this->repoUsers = $users;

        parent::__construct($httpRequest);
    }


    function __invoke($token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Validate Sent Data:
        #
        $post = ParseRequestData::_($this->request)->parse();
        $post = $this->_assertValidData($post);


        # Get Identifier Match
        #
        $identifier = IdentifierObject::newIdentifierByType(key($post), current($post));
        $identifier->setValidated();

        /** @var iOAuthUser $userEntity */
        $userEntity = $this->repoUsers->findOneMatchByIdentifiers([ $identifier ]);

        # Build Response
        #
        if (! $userEntity ) {
            // Indicate no Content
            $response = new HttpResponse;
            $response->setStatusCode(204);
            $r = $response;

        } else {

            $r = array(
                'uid' => (string) $userEntity->getUid(),
                'profile' => [
                    'fullname' => $userEntity->getFullName(),
                    'username' => $userEntity->getUsername(),
                ],
            );
        }


        return array(
            ListenerDispatch::RESULT_DISPATCH => $r,
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
            throw new \InvalidArgumentException('Too Many Parameters', 400);

        return $post;
    }
}
