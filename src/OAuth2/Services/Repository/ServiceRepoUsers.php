<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Driver\Mongo\UserRepo;
use Module\OAuth2\Services\BuildServices;


class ServiceRepoUsers
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildServices::USERS;

    # Using alternate client
    # protected $mongoClient = 'prod1';


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return UserRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new UserRepo($mongoDb, $collection, $persistable);
    }
}
