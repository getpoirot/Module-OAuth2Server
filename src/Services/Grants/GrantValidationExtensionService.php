<?php
namespace Module\OAuth2\Services\Grants;

use Poirot\OAuth2\Server\Grant\GrantExtensionTokenValidation;


class GrantValidationExtensionService
    extends aGrantService
{
    const GrantType = GrantExtensionTokenValidation::GrantType;
    protected $name = self::GrantType;


    /**
     * Get Grant Classname
     *
     * @return string
     */
    function getGrantClassname()
    {
        return GrantExtensionTokenValidation::class;
    }
}
