<?php
namespace Module\OAuth2\Services;

use Poirot\Application\aSapi;
use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\OAuth2\Server\Grant\GrantAggregateGrants;
use Poirot\Std\Struct\DataEntity;

class ServiceGrantResponder 
    extends aServiceContainer
{
    const CONF_KEY = 'grant_responder';

    protected $name = 'GrantResponder';
    
    
    /**
     * Create Service
     *
     * @return mixed
     */
    function newService()
    {
        $sc     = $this->services();
        /** @var aSapi $sapi */
        $sapi   = $sc->get('/sapi');
        /** @var DataEntity $config */
        $config = $sapi->config();
        $config = $config->get(\Module\OAuth2\Module::CONF_KEY);

        $settings = null;
        if (is_array($config) && isset($config[self::CONF_KEY])) {
            $settings = $config[self::CONF_KEY];
            $settings = \Poirot\Config\instanceInitialized($settings);
        }

        $grantsAggregate = new GrantAggregateGrants;
        $grantsAggregate->with($grantsAggregate::parseWith($settings));
        return $grantsAggregate;
    }
}
