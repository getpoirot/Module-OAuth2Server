<?php
namespace Module\OAuth2\Interfaces\Model\Repo;

use Module\OAuth2\Interfaces\Model\iOAuthUser;
use Module\OAuth2\Interfaces\Model\iUserIdentifierObject;
use Poirot\OAuth2\Interfaces\Server\Repository\iOAuthUser as BaseEntityUser;
use Poirot\OAuth2\Interfaces\Server\Repository\iRepoUsers as BaseRepoUsers;


interface iRepoUsers
    extends BaseRepoUsers
{
    /**
     * Generate next unique identifier to persist
     * data with
     *
     * @param null|string $id
     *
     * @return mixed
     */
    function attainNextIdentifier($id = null);

    /**
     * Used When Persistence want to store credential
     * or match given plain hash with persistence
     *
     * exp. md5(password) = stored_password
     *
     * @param string $credential
     * 
     * @return mixed
     */
    function makeCredentialHash($credential);
    
    /**
     * Insert User Entity
     *
     * @param iOAuthUser $user
     *
     * @return BaseEntityUser
     */
    function insert(iOAuthUser $user);

    /**
     * Has Identifier Existed?
     * return identifiers from list that has picked by someone or empty list
     *
     * @param []iEntityUserIdentifierObject $identifier
     *
     * @return []iEntityUserIdentifierObject
     */
    function hasAnyIdentifiersRegistered(array $identifiers);

    /**
     * Find Match With Exact Identifiers?
     *
     * @param iUserIdentifierObject[] $identifiers
     *
     * @return iOAuthUser|false
     */
    function findOneMatchByIdentifiers(array $identifiers);

    /**
     * Find Match With Exact Identifier Value
     *
     * @param string|array|\Traversable $value
     *
     * @return iOAuthUser|false
     */
    function findOneHasIdentifierWithValue($value);

    /**
     * Update Identifier Type Of Given User to Validated
     *
     * @param string $uid User Identifier
     * @param string $identifierType
     *
     * @return int Affected Rows
     */
    function updateUserIdentifierAsValidated($uid, $identifierType);

    /**
     * Set Identifier Type Of Given User
     *
     * !! delete and add new identifier
     *
     * @param string $uid User Identifier
     * @param string $identifierType
     * @param mixed  $value
     * @param bool   $validated
     *
     * @return int Affected Rows
     */
    function setUserIdentifier($uid, $identifierType, $value, $validated = false);

    /**
     * Update Specific Grant Type By Given Value
     *
     * !! used to change password or specific credential of user
     *
     * @param string $uid
     * @param string $grantType
     * @param string $grantValue
     *
     * @return int Affected Rows
     */
    function updateGrantTypeValue($uid, $grantType, $grantValue);

    /**
     * Delete Entity By Identifier
     *
     * @param string  $uid
     * @param boolean $validated  Validated Only?
     *
     * @return int Deleted Count
     */
    function deleteByUID($uid, $validated);
}
