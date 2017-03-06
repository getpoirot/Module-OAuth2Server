<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Model\Mongo\RefreshTokens;
use Module\OAuth2\Services\BuildOAuthModuleServices;


class ServiceRepoRefreshTokens
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildOAuthModuleServices::SERVICE_NAME_REFRESH_TOKENS;


    /**
     * Return new instance of Repository
     *
     * @param \MongoDB\Database $mongoDb
     * @param string           $collection
     *
     * @return RefreshTokens
     */
    function newRepoInstance($mongoDb, $collection)
    {
        return new RefreshTokens($mongoDb, $collection);
    }
}
