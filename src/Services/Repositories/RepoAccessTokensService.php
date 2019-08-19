<?php
namespace Module\OAuth2\Services\Repositories;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Driver\Mongo\AccessTokenRepo;


class RepoAccessTokensService
    extends aServiceRepository
{
    /**
     * @inheritDoc
     * @return AccessTokenRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new AccessTokenRepo($mongoDb, $collection, $persistable);
    }
}
