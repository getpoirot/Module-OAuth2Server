<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Driver\Mongo\ValidationRepo;
use Module\OAuth2\Services\BuildServices;


class ServiceRepoValidationCodes
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildServices::VALIDATION_CODES;


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return ValidationRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new ValidationRepo($mongoDb, $collection, $persistable);
    }
}
