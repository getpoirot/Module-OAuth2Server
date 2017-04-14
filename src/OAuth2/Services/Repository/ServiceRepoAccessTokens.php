<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Mongo\AccessTokens;
use Module\OAuth2\Services\BuildServices;


class ServiceRepoAccessTokens
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildServices::ACCESS_TOKENS;


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database  $mongoDb
     * @param string             $collection
     * @param string|object|null $persistable
     *
     * @return AccessTokens
     */
    function newRepoInstance($mongoDb, $collection, $persistable = null)
    {
        return new AccessTokens($mongoDb, $collection, $persistable);
    }
}
