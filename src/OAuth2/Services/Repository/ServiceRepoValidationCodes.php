<?php
namespace Module\OAuth2\Services\Repository;

use Module\MongoDriver\Services\aServiceRepository;
use Module\OAuth2\Services\BuildOAuthModuleServices;

class ServiceRepoValidationCodes
    extends aServiceRepository
{
    /** @var string Service Name */
    protected $name = BuildOAuthModuleServices::SERVICE_NAME_VALIDATION_CODES;

    
    /**
     * Repository Class Name
     *
     * @return string
     */
    function getRepoClassName()
    {
        return \Module\OAuth2\Model\Mongo\ValidationCodes::class;
    }
}
