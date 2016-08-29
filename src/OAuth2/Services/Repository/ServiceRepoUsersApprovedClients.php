<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;

class ServiceRepoUsersApprovedClients
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'users.approved_clients';

    
    /**
     * Repository Class Name
     *
     * @return string
     */
    function getRepoClassName()
    {
        return \Module\OAuth2\Model\Repo\Mongo\Users\ApprovedClients::class;
    }
}
