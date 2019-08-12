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

    protected $defaultGrants = [];


    /**
     * Create Service
     *
     * @return ContainerGrantsCapped
     */
    function newService()
    {
        $services = $this->_getConf();
        $services = [
            'services' => array_merge( $this->defaultGrants, $services )
        ];

        $builder = new BuildContainer;
        $builder->with( $builder::parseWith($services) );

        $plugins = new ContainerGrantsCapped($builder);
        return $plugins;
    }


    // ..

    /**
     * @param array $defaultGrants
     */
    function setDefaultGrants($defaultGrants)
    {
        $this->defaultGrants = $defaultGrants;
    }

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

        $config = $config[self::CONF]['grants'];
        return $config;
    }
}
