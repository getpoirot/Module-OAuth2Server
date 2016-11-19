<?php
namespace Module\OAuth2\Model\Mongo;

use Module\MongoDriver\Model\Repository\aRepository;
use Module\OAuth2\Interfaces\Model\iEntityValidationCode;
use Module\OAuth2\Interfaces\Model\Repo\iRepoValidationCodes;

class ValidationCodes extends aRepository
    implements iRepoValidationCodes
{
    /**
     * Initialize Object
     *
     */
    protected function __init()
    {
        $this->setModelPersist(new ValidationCode);
    }

    /**
     * Insert Validation Code
     *
     * @param iEntityValidationCode $validationCode
     *
     * @return iEntityValidationCode
     */
    function insert(iEntityValidationCode $validationCode)
    {
        $e = new ValidationCode; // use object model persist
        $e  ->setUserIdentifier($validationCode->getUserIdentifier())
            ->setValidationCode($validationCode->getValidationCode())
            ->setAuthCodes($validationCode->getAuthCodes())
            ->setExpirationDateTime($validationCode->getExpirationDateTime())
        ;

        $r = $this->_query()->insertOne($e);

        // TODO return iEntityValidationCode interface now data returned contains Specific Mongo Object Model
        return $e;
    }

    /**
     * Find Match By Given Validation Code
     *
     * @param string $validationCode
     *
     * @return iEntityValidationCode|false
     */
    function findOneByValidationCode($validationCode)
    {
        $r = $this->_query()->findOne([
            'validation_code' => $validationCode,
        ]);

        return $r ? $r : false;
    }
}
