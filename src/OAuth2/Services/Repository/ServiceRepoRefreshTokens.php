<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Driver\Mongo\RefreshTokenRepo;
use Module\OAuth2\Services\BuildServices;


class ServiceRepoRefreshTokens
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildServices::REFRESH_TOKENS;


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return RefreshTokenRepo
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new RefreshTokenRepo($mongoDb, $collection, $persistable);
    }
}
