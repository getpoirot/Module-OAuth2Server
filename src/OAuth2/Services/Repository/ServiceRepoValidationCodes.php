<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Mongo\ValidationCodes;
use Module\OAuth2\Services\BuildOAuthModuleServices;


class ServiceRepoValidationCodes
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildOAuthModuleServices::SERVICE_VALIDATION_CODES;


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return ValidationCodes
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new ValidationCodes($mongoDb, $collection, $persistable);
    }
}
