<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Mongo\Users;
use Module\OAuth2\Services\BuildOAuthModuleServices;

class ServiceRepoUsers
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildOAuthModuleServices::SERVICE_USERS;


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return Users
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new Users($mongoDb, $collection, $persistable);
    }
}
