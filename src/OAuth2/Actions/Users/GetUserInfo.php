<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Interfaces\Model\iEntityUserIdentifierObject;
use Module\OAuth2\Model\Mongo\Users;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class GetUserInfo extends aAction
{
    /**
     * @param string             $uid
     * @param iEntityAccessToken $token
     *
     * @return array
     */
    function __invoke($uid = null, $token = null)
    {
        if ($uid === null)
            // Retrieve from token
            $uid = $token->getOwnerIdentifier();

        /** @var Users $repoUsers */
        $repoUsers = $this->IoC()->get('services/repository/Users');
        /** @var iEntityUser $u */
        if ( ($uid == null) || !($u = $repoUsers->findOneByUID($uid)) )
            throw new exRouteNotMatch('User not Found.');

        $userInfo = [
            'username'     => $u->getUsername(),
            'fullname'     => $u->getFullName(),
            'date_created' => $u->getDateCreated(),
        ];

        /** @var iEntityUserIdentifierObject $identifier */
        foreach ($u->getIdentifiers() as $identifier)
            $userInfo[$identifier->getType()] = $identifier->getValue();

        return [
            ListenerDispatch::RESULT_DISPATCH => $userInfo,
        ];
    }
}
