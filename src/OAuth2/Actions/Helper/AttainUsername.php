<?php
namespace Module\OAuth2\Actions\Helper;

use Module\OAuth2\Actions\Helper\AttainUsername\NamesGenerator;
use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
use Module\OAuth2\Model\Entity\User\IdentifierObject;


class AttainUsername
{
    /** @var iRepoUsers */
    protected $repoUsers;


    /**
     * AttainUsername constructor.
     *
     * @param iRepoUsers $users @IoC /module/oauth2/services/repository/Users
     */
    function __construct(iRepoUsers $users)
    {
        $this->repoUsers = $users;
    }


    /**
     * Attain Next Username For Given User Entity
     *
     * @param iOAuthUser|null $user
     *
     * @return string
     */
    function __invoke(iOAuthUser $user = null)
    {
        if (! $username = $user->getUsername() )
            $username = (string) new NamesGenerator($user->getFullName());

        $i        = null;
        do {
            $username .= (string) $i;
            $identifier = new IdentifierObject;
            $identifier->setType('username');
            $identifier->setValue($username);

            $goNext = $this->repoUsers->hasAnyIdentifiersRegistered( [$identifier] );
            $i++;
        } while ($goNext);

        return $username;
    }
}
