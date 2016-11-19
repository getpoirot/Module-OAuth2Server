<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;

class ServiceRepoUsersApprovedClients
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'Users.ApprovedClients';

    
    /**
     * Repository Class Name
     *
     * @return string
     */
    function getRepoClassName()
    {
        return \Module\OAuth2\Model\Mongo\Users\ApprovedClients::class;
    }
}
