<?php
namespace Module\OAuth2\Services;

use Module\OAuth2\Module;
use Poirot\Ioc\Container\BuildContainer;
use Poirot\Ioc\Container\Service\aServiceContainer;


class GrantPluginsService
    extends aServiceContainer
{
    /**
     * @inheritDoc
     */
    function newService()
    {
        $plugins = new GrantPlugins;
        $this->_addGrantsFromModuleConfig($plugins);
        return $plugins;
    }

    // ..

    /**
     * Add Grants From Module Config
     *
     * @param GrantPlugins $plugins
     * @throws \Exception
     */
    protected function _addGrantsFromModuleConfig(GrantPlugins $plugins)
    {
        if ($config  = \Poirot\config(Module::class, 'grants', 'plugins')) {
            $builder = new BuildContainer( BuildContainer::parseWith($config) );
            $builder->build($plugins);
        }
    }
}
