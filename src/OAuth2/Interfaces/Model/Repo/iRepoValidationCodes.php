<?php
namespace Module\OAuth2\Interfaces\Model\Repo;

use Module\OAuth2\Interfaces\Model\iEntityValidationCode;


interface iRepoValidationCodes
{
    /**
     * Insert Validation Code
     *
     * @param iEntityValidationCode $validationCode
     *
     * @return iEntityValidationCode
     */
    function insert(iEntityValidationCode $validationCode);

    /**
     * Find Match By Given Validation Code
     *
     * @param string $validationCode
     *
     * @return iEntityValidationCode|false
     */
    function findOneByValidationCode($validationCode);
}
