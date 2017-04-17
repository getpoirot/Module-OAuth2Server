<?php
namespace Module\OAuth2\Interfaces\Model\Repo;

use Module\OAuth2\Interfaces\Model\iValidation;


interface iRepoValidationCodes
{
    /**
     * Insert Validation Code
     *
     * note: each user must has one validation code persistence at time
     *       "user_identifier" is unique
     *
     * @param iValidation $validationCode
     *
     * @return iValidation
     */
    function insert(iValidation $validationCode);

    /**
     * Find Match By Given Validation Code
     *
     * note: consider expiration time
     *
     * @param string $validationCode
     *
     * @return iValidation|false
     */
    function findOneByValidationCode($validationCode);

    /**
     * Find Match By Given User Identifier
     *
     * note: consider expiration time
     *
     * @param string $userIdentifier
     *
     * @return iValidation|false
     */
    function findOneByUserIdentifier($userIdentifier);

    /**
     * Find Match For User Identifier That Has Specific Identifier Type
     * Validation Code Generated
     *
     * note: consider expiration time
     *
     * @param string $userIdentifier
     * @param string $identifierType
     *
     * @return false|iValidation
     */
    function findOneHasAuthCodeMatchUserType($userIdentifier, $identifierType);

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
     * @param string $vid
     * @param string $authType
     *
     * @return int Affected Rows
     */
    function updateAuthAsValidated($vid, $authType);

    /**
     * Update Sent DateTime Data Of AuthCode Type From Given Validation Code
     * To Current Time
     *
     * @param string $validationCode
     * @param string $authType
     *
     * @return int Affected Rows
     */
    function updateAuthTimestampSent($validationCode, $authType);
}
