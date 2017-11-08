<?php
namespace Module\OAuth2\Actions\Api;

use Module\HttpFoundation\Events\Listener\ListenerDispatch;
use Module\OAuth2\Actions\aApiAction;
use Module\OAuth2\Exception\exUserNotFound;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity\User\IdentifierObject;
use Poirot\Http\Interfaces\iHttpRequest;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;
use Poirot\Std\Type\StdTravers;


class GetUserInfoRequest
    extends aApiAction
{
    protected $tokenMustHaveOwner  = false;
    protected $tokenMustHaveScopes = array(

    );

    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * Construct
     *
     * @param iRepoUsers           $users       @IoC /module/oauth2/services/repository/Users
     * @param iHttpRequest         $httpRequest @IoC /HttpRequest
     */
    function __construct(iRepoUsers $users, iHttpRequest $httpRequest)
    {
        $this->repoUsers = $users;

        parent::__construct($httpRequest);
    }


    /**
     * Get current user info identified with given token
     *
     * @param iEntityAccessToken $token
     * @param string             $username Uri param
     * @param string             $userid   Uri param
     *
     * @return array
     * @throws \Exception
     */
    function __invoke($token = null, $username = null, $userid = null)
    {
        # Assert Token
        #
        $this->assertTokenByOwnerAndScope($token);

        if ($username !== null) {
            $username = trim($username);
            $userEntity = $this->repoUsers->findOneMatchByIdentifiers([
                IdentifierObject::newUsernameIdentifier($username)
            ]);
        }
        elseif ($userid !== null) {
            $userid     = trim($userid);
            $userEntity = $this->repoUsers->findOneByUID($userid);
        }
        else {
            if (! $identifier = $token->getOwnerIdentifier())
                throw new \Exception('Token Identifier is Empty!!');

            $userEntity = $this->repoUsers->findOneByUID( $identifier );
        }


        # Retrieve User With OwnerID
        #
        /** @var iOAuthUser $userEntity */
        if (! $userEntity)
            throw new exUserNotFound('User not Found.');


        # Build Response
        #
        $userInfo = [];

        $isValidAll = true; $validated = array();
        foreach ($userEntity->getIdentifiers() as $identifier) {
                // embed identifiers validity
                $isValidAll &= $identifier->isValidated();
                $validated[$identifier->getType()] = (boolean) $identifier->isValidated();

            $val = $identifier->getValue();
            if ($val instanceof \Traversable)
                $userInfo[$identifier->getType()] = StdTravers::of($identifier->getValue())->toArray();
            else
                $userInfo[$identifier->getType()] = $identifier->getValue();
        }

        $userInfo['meta'] = $userEntity->getMeta();

        $userInfo['datetime_created']  = [
            'datetime'  => $userEntity->getDateCreated(),
            'timestamp' => $userEntity->getDateCreated()->getTimestamp(),
        ];


        return [
            ListenerDispatch::RESULT_DISPATCH => [
                'user' => [
                    'uid'      => (string) $userEntity->getUid(),
                    'fullname' => $userEntity->getFullName(),
                ] + $userInfo,
                'is_valid'      => (boolean) $isValidAll,
                'is_valid_more' => $validated,
            ],
        ];
    }
}
