<?php
namespace Module\OAuth2\Services;

use Poirot\Application\aSapi;
use Poirot\Ioc\Container\BuildContainer;
use Poirot\Ioc\Container\Service\aServiceContainer;
use Poirot\Std\Struct\DataEntity;


class ServiceGrantsContainer
    extends aServiceContainer
{
    const CONF = 'module.oauth2.grants';
    const NAME = 'ContainerGrants';

    /** @var string Service Name */
    protected $name = self::NAME;


    /**
     * Create Service
     *
     * @return ContainerGrantsCapped
     */
    function newService()
    {
        $builder = new BuildContainer;
        $builder->with($builder::parseWith($this->_getConf()));

        $plugins = new ContainerGrantsCapped($builder);
        return $plugins;
    }


    // ..

    /**
     * Get Config Values
     *
     * @return mixed|null
     * @throws \Exception
     */
    protected function _getConf()
    {
        // retrieve and cache config
        $services = $this->services();

        /** @var aSapi $config */
        $config = $services->get('/sapi');
        $config = $config->config();
        /** @var DataEntity $config */
        $config = $config->get( \Module\OAuth2\Module::CONF_KEY, array() );

        if (!isset($config[self::CONF]) && !is_array($config[self::CONF]))
            throw new \Exception('OAuth2 Module, Grants Services Config Not Available.');


        $config = $config[self::CONF];
        return $config;
    }
}
