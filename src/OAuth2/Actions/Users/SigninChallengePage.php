<?php
namespace Module\OAuth2\Actions\Users;

use Module\OAuth2\Actions\aAction;
use Module\OAuth2\Interfaces\Model\iEntityUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Poirot\Application\Exception\exRouteNotMatch;


class SigninChallengePage
    extends aAction
{
    const FLASH_MESSAGE_ID = 'SigninChallengePage';

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

    /**
     * @param string $uid        User UID
     * @param string $identifier Identifier type; exp. "email"
     */
    function __invoke($uid = null, $identifier = null)
    {
        kd(func_get_args());

        /** @var iEntityUser $user */
        $user = $this->repoUsers->findOneByUID($uid);
        if (!$user)
            throw new exRouteNotMatch;

        $userIdentifiers = $user->getIdentifiers();

        kd($userIdentifiers);


    }
}
