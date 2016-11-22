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
     * Delete Entity By Identifier
     *
     * @param string  $uid
     * @param boolean $validated  Validated Only?
     *
     * @return int Deleted Count
     */
    function deleteByUID($uid, $validated);

    /**
     * Is Identifier Existed?
     *
     * @param []iEntityUserIdentifierObject $identifier
     *
     * @return boolean
     */
    function isIdentifiersRegistered(array $identifiers);

    /**
     * Find Match With Exact Identifiers?
     *
     * @param array   $identifiers
     * @param boolean $allValidated
     *
     * @return iEntityUser|false
     */
    function findOneByIdentifiers(array $identifiers, $allValidated = null);

    /**
     * Update Identifier Type Of Given User to Validated
     *
     * @param string $uid User Identifier
     * @param string $identifierType
     *
     * @return int Affected Rows
     */
    function updateIdentifierAsValidated($uid, $identifierType);
}
