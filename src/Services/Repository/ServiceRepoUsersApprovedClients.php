<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Driver\Mongo\Users\ApprovedClientRepo;


class ServiceRepoUsersApprovedClients
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'Users.ApprovedClients';


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return ApprovedClientRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new ApprovedClientRepo($mongoDb, $collection, $persistable);
    }
}
