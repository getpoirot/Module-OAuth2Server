<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Mongo\Clients;
use Module\OAuth2\Services\BuildOAuthModuleServices;

class ServiceRepoClients
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildOAuthModuleServices::SERVICE_CLIENTS;


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return Clients
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new Clients($mongoDb, $collection, $persistable);
    }
}
