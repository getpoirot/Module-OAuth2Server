<?php
namespace Module\OAuth2\Services\Grants;

use Poirot\OAuth2\Server\Grant\GrantRefreshToken;


class GrantRefreshTokenService
    extends aGrantService
{
    protected $defaultSettings = [];

    function __init()
    {
        parent::__init();

        $this->defaultSettings = [
            'ttl_refresh_token' => new \DateInterval('P1M'),
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
        return GrantRefreshToken::class;
    }
}
