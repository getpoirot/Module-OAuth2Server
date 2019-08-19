<?php
namespace Module\OAuth2\Services\Repositories;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Driver\Mongo\Users\ApprovedClientRepo;


class RepoUsersApprovedClientsService
    extends aServiceRepository
{
    /**
     * @inheritDoc
     * @return ApprovedClientRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new ApprovedClientRepo($mongoDb, $collection, $persistable);
    }
}
