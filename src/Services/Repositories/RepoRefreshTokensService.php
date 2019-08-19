<?php
namespace Module\OAuth2\Services\Repositories;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Driver\Mongo\RefreshTokenRepo;


class RepoRefreshTokensService
    extends aServiceRepository
{
    /**
     * @inheritDoc
     * @return RefreshTokenRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new RefreshTokenRepo($mongoDb, $collection, $persistable);
    }
}
