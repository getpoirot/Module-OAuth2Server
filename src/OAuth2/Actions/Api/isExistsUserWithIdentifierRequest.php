<?php
namespace Module\OAuth2\Actions\Api;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Poirot\Http\HttpMessage\Request\Plugin;
use Module\OAuth2\Actions\aApiAction;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\Std\Exceptions\exUnexpectedValue;


class isExistsUserWithIdentifierRequest
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


    /**
     * Check that user with given identity exist?
     *
     * @param iEntityAccessToken $token
     *
     * @return array [ name => (bool) ]
     */
    function __invoke($token = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);


        # Parse and Validate Sent Data
        #
        // TODO using phpServer Post->get
        $post = Plugin\ParseRequestData::_($this->request)->parse();
        $post = self::_assertValidData($post);

        $identifiers = $post['identifiers'];


        # Build Response
        #
        $return = [];
        /** @var iUserIdentifierObject $ident */
        foreach ($identifiers as $ident) {
            $r = $this->repoUsers->hasAnyIdentifiersRegistered([ $ident ]);
            $return[$ident->getType()] = (boolean) $r;
        }

        return [
            ListenerDispatch::RESULT_DISPATCH => $return,
        ];
    }

    /**
     * Assert Validated Change Identifier
     *
     * Array (
     *   [username] => 'payam.naderi'
     * )
     *
     * @param array $post
     *
     * @return iUserIdentifierObject[]
     */
    protected static function _assertValidData(array $post)
    {
        # Validate Data:

        # Sanitize Data:
        $identifiers = [];
        foreach ($post as $k => $v) {
            try {
                $identifiers[] = IdentifierObject::newIdentifierByType($k, $v);
            } catch (\Exception $e) {
                throw new exUnexpectedValue(sprintf(
                    'Identifier type (%s) can`t fulfilled.'
                    , $k
                ));
            }
        }

        return ['identifiers' => $identifiers];
    }
}
