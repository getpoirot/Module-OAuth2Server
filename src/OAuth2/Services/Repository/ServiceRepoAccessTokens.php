<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Mongo\AccessTokens;
use Module\OAuth2\Services\BuildOAuthModuleServices;


class ServiceRepoAccessTokens
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildOAuthModuleServices::SERVICE_NAME_ACCESS_TOKENS;


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database $mongoDb
     * @param string           $collection
     *
     * @return AccessTokens
     */
    function newRepoInstance($mongoDb, $collection)
    {
        return new AccessTokens($mongoDb, $collection);
    }
}
