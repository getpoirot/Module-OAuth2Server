<?php
namespace Module\OAuth2\Services\Repositories;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Driver\Mongo\UserRepo;


class RepoUsersService
    extends aServiceRepository
{
    /**
     * @inheritDoc
     * @return UserRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new UserRepo($mongoDb, $collection, $persistable);
    }
}
