<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;

class ServiceRepoClients
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = 'clients';

    
    /**
     * Repository Class Name
     *
     * @return string
     */
    function getRepoClassName()
    {
        return \Module\OAuth2\Model\Repo\Mongo\Clients::class;
    }
}
