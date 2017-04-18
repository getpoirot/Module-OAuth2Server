<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Module\OAuth2\Model\Mongo\Users;
use Poirot\Application\Exception\exRouteNotMatch;
use Poirot\Application\Sapi\Server\Http\ListenerDispatch;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityAccessToken;


class GetUserInfo extends aAction
{
    /**
     * @param string $uid
     *
     * @return array
     */
    function __invoke($uid = null, $checkIsValidID = null)
    {
        /** @var Users $repoUsers */
        $repoUsers = $this->moduleServices()->get('services/repository/Users');
        /** @var iOAuthUser $u */
        if ( ($uid == null) || !($u = $repoUsers->findOneByUID($uid)) )
            throw new exRouteNotMatch('User not Found.');

        $userInfo = [ 'fullname' => $u->getFullName() ];

        /** @var iUserIdentifierObject $identifier */
        if ($checkIsValidID)
            $validated = array();

        $isValidAll = true;
        foreach ($u->getIdentifiers() as $identifier) {
            if ($checkIsValidID) {
                // embed identifiers validaty
                $isValidAll &= $identifier->isValidated();
                $validated[$identifier->getType()] = (boolean) $identifier->isValidated();
            }

            $userInfo[$identifier->getType()] = $identifier->getValue();
        }

        (!isset($validated))       ?: $userInfo['is_valid']      = (boolean) $isValidAll;
        (!isset($validated))       ?: $userInfo['is_valid_more'] = $validated;
        $userInfo['date_created']  = $u->getDateCreated();

        return [
            ListenerDispatch::RESULT_DISPATCH => $userInfo,
        ];
    }

    
    // ..
    
    /**
     * Used With Chained Actions To Extract Data From Request
     *
     */
    static function functorParseUidFromToken()
    {
        /**
         * Token from token assertion
         * @see \Module\OAuth2\assertAuthToken() 
         * 
         * @param iEntityAccessToken $token
         * @return array
         */
        return function (iEntityAccessToken $token = null) {
            $uid = $token->getOwnerIdentifier();
            return ['uid' => $uid];
        };
    }
}
