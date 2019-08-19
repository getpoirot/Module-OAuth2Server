<?php
namespace Module\OAuth2\Services\Grants;

use Module\OAuth2\Services\Grants\aGrantService;
use Poirot\OAuth2\Server\Grant\GrantClientCredentials;


class GrantClientCredentialService
    extends aGrantService
{
    protected $defaultSettings = [];

    function __init()
    {
        parent::__init();

        $this->defaultSettings = [
            'ttl_access_token' => new \DateInterval('PT1H'),
        ];
    }
    
    
    /**
     * Get Grant Classname
     *
     * @return string
     */
    function getGrantClassname()
    {
        return GrantClientCredentials::class;
    }
}
