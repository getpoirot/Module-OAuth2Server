<?php
namespace Module\OAuth2\Interfaces\Model\Repo;

use Module\OAuth2\Interfaces\Model\iEntityValidationCode;


interface iRepoValidationCodes
{
    /**
     * Insert Validation Code
     *
     * note: each user must has one validation code persistence at time
     *       "user_identifier" is unique
     *
     * @param iEntityValidationCode $validationCode
     *
     * @return iEntityValidationCode
     */
    function insert(iEntityValidationCode $validationCode);

    /**
     * Find Match By Given Validation Code
     *
     * note: consider expiration time
     *
     * @param string $validationCode
     *
     * @return iEntityValidationCode|false
     */
    function findOneByValidationCode($validationCode);

    /**
     * Find Match By Given User Identifier
     *
     * note: consider expiration time
     *
     * @param string $userIdentifier
     *
     * @return iEntityValidationCode|false
     */
    function findOneByUserIdentifier($userIdentifier);

    /**
     * Delete Entity By Identifier
     *
     * @param string $validationCode
     *
     * @return int Deleted Count
     */
    function deleteByValidationCode($validationCode);

    /**
     * Update Authorization Type Of Given Validation Code
     * to Validated
     *
     * @param string $validationCode
     * @param string $authType
     *
     * @return int Affected Rows
     */
    function updateAuthCodeAsValidated($validationCode, $authType);
}