<?php
namespace Module\OAuth2\Interfaces\Model\Repo;

use Module\OAuth2\Interfaces\Model\iEntityUser;
use Poirot\OAuth2\Interfaces\Server\Repository\iEntityUser as BaseEntityUser;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoUsers as BaseRepoUsers;

interface iRepoUsers
    extends BaseRepoUsers
{
    /**
     * Insert User Entity
     *
     * @param iEntityUser $user
     *
     * @return BaseEntityUser
     */
    function insert(iEntityUser $user);

    /**
     * Is Identifier Existed?
     *
     * @param []iEntityUserIdentifierObject $identifier
     *
     * @return boolean
     */
    function isExistsIdentifiers(array $identifiers);
}
