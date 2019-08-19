<?php
namespace Module\OAuth2\Services\Repositories;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Driver\Mongo\ClientRepo;


class RepoClientsService
    extends aServiceRepository
{
    /**
     * @inheritDoc
     * @return ClientRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new ClientRepo($mongoDb, $collection, $persistable);
    }
}
