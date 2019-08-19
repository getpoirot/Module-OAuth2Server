<?php
namespace Module\OAuth2\Services\Grants;

use Poirot\OAuth2\Server\Grant\GrantImplicit;


class GrantImplicitService
    extends aGrantService
{
    protected $defaultSettings = [];

    function __init()
    {
        parent::__init();

        $this->defaultSettings = [
            'ttl_access_token' => new \DateInterval('PT1H'),
            'retrieve_user_callback' => \Module\OAuth2\Actions\IOC::bareService()->RetrieveAuthenticatedUser,
        ];
    }


    /**
     * Get Grant Classname
     *
     * @return string
     */
    function getGrantClassname()
    {
        return GrantImplicit::class;
    }
}
