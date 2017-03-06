<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Mongo\ValidationCodes;
use Module\OAuth2\Services\BuildOAuthModuleServices;


class ServiceRepoValidationCodes
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildOAuthModuleServices::SERVICE_NAME_VALIDATION_CODES;


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database $mongoDb
     * @param string           $collection
     *
     * @return ValidationCodes
     */
    function newRepoInstance($mongoDb, $collection)
    {
        return new ValidationCodes($mongoDb, $collection);
    }
}
