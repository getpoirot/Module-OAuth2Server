<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;

class ServiceRepoUsers
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'users';

    
    /**
     * Repository Class Name
     *
     * @return string
     */
    function getRepoClassName()
    {
        return \Module\OAuth2\Model\Repo\Mongo\Users::class;
    }
}
