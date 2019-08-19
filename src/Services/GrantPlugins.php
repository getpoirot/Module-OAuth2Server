<?php
namespace Module\OAuth2\Services;

use Poirot\Ioc\Container\aContainerCapped;
use Poirot\Ioc\Container\BuildContainer;
use Poirot\Ioc\Container\Exception\exContainerInvalidServiceType;
use Poirot\Ioc\Container\Service\ServicePluginLoader;
use Poirot\Loader\LoaderMapResource;
use Poirot\OAuth2\Interfaces\Server\iGrant;


class GrantPlugins
    extends aContainerCapped
{
    protected $_map_resolver_options = [
        #'plain'   => PathToClass::class,
    ];


    /**
     * @inheritDoc
     */
    function __construct(BuildContainer $cBuilder = null)
    {
        $this->_attachDefaults();

        parent::__construct($cBuilder);
    }


    /**
     * @inheritDoc
     */
    function validateService($pluginInstance)
    {
        if (! is_object($pluginInstance) )
            throw new \Exception(sprintf('Can`t resolve to (%s) Instance.', $pluginInstance));

        if (! $pluginInstance instanceof iGrant )
            throw new exContainerInvalidServiceType('Invalid Plugin Of Content Object Provided.');
    }

    // ..

    protected function _attachDefaults()
    {
        $service = new ServicePluginLoader([
            'resolver_options' => [
                LoaderMapResource::class => $this->_map_resolver_options
            ],
        ]);

        $this->set($service);
    }
}
