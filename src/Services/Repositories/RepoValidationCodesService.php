<?php
namespace Module\OAuth2\Services\Repositories;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Driver\Mongo\ValidationRepo;


class RepoValidationCodesService
    extends aServiceRepository
{
    /**
     * @inheritDoc
     * @return ValidationRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new ValidationRepo($mongoDb, $collection, $persistable);
    }
}
