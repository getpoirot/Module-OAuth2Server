<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Driver\Mongo\ClientRepo;
use Module\OAuth2\Services\BuildServices;


class ServiceRepoClients
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildServices::CLIENTS;


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return ClientRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new ClientRepo($mongoDb, $collection, $persistable);
    }
}
