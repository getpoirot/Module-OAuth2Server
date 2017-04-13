<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Mongo\Users\ApprovedClients;

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
     * @return ApprovedClients
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new ApprovedClients($mongoDb, $collection, $persistable);
    }
}
