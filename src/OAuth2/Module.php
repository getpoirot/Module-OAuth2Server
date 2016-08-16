<?php
namespace Module\OAuth2;

use Poirot\Application\Interfaces\Sapi\iSapiModule;
use Poirot\Application\ModuleManager\Interfaces\iModuleManager;
use Poirot\Application\Interfaces\Sapi;

use Poirot\Ioc\Container;

use Poirot\Loader\Autoloader\LoaderAutoloadAggregate;
use Poirot\Loader\Autoloader\LoaderAutoloadNamespace;
use Poirot\Loader\Interfaces\iLoaderAutoload;
use Poirot\Loader\LoaderAggregate;

use Poirot\Loader\LoaderNamespaceStack;
use Poirot\Router\BuildRouterStack;
use Poirot\Router\Interfaces\iRouterStack;

use Poirot\Std\Interfaces\Struct\iDataEntity;


/**
 * [] Exception Handler
 * [] Repositories
 * [] EndPoints
 * [] Grant Types
 * [] Server
 */

class Module implements iSapiModule
    , Sapi\Module\Feature\iFeatureModuleAutoload
    , Sapi\Module\Feature\iFeatureModuleInitModuleManager
    , Sapi\Module\Feature\iFeatureOnPostLoadModulesGrabServices
    , Sapi\Module\Feature\iFeatureModuleMergeConfig
{
    /**
     * Register class autoload on Autoload
     *
     * priority: 1000 B
     *
     * @param LoaderAutoloadAggregate $baseAutoloader
     *
     * @return iLoaderAutoload|array|\Traversable|void
     */
    function initAutoload(LoaderAutoloadAggregate $baseAutoloader)
    {
        #$nameSpaceLoader = \Poirot\Loader\Autoloader\LoaderAutoloadNamespace::class;
        $nameSpaceLoader = 'Poirot\Loader\Autoloader\LoaderAutoloadNamespace';
        /** @var LoaderAutoloadNamespace $nameSpaceLoader */
        $nameSpaceLoader = $baseAutoloader->loader($nameSpaceLoader);
        $nameSpaceLoader->addResource('Poirot\OAuth2', __DIR__. '/../../vendor/poirot/');
        
        require_once __DIR__ . '/../../vendor/poirot/Poirot/OAuth2/_functions.php';
    }

    /**
     * Initialize Module Manager
     *
     * priority: 1000 C
     *
     * @param iModuleManager $moduleManager
     *
     * @return void
     */
    function initModuleManager(iModuleManager $moduleManager)
    {
        if (!$moduleManager->hasLoaded('Authorization'))
            // Authorization Module Is Required.
            $moduleManager->loadModule('Authorization');

    }

    /**
     * Register config key/value
     *
     * priority: 1000 D
     *
     * - you may return an array or Traversable
     *   that would be merge with config current data
     *
     * @param iDataEntity $config
     *
     * @return array|\Traversable
     */
    function initConfig(iDataEntity $config)
    {
        return include __DIR__.'/../../config/module.conf.php';
    }

    /**
     * Resolve to service with name
     *
     * - each argument represent requested service by registered name
     *   if service not available default argument value remains
     * - "services" as argument will retrieve services container itself.
     *
     * ! after all modules loaded
     *
     * @param iRouterStack                   $router
     *
     * @internal param null $services service names must have default value
     */
    function resolveRegisteredServices(
        $router = null
    ) {
        # Register Http Routes:
        if ($router) {
            $routes = include __DIR__.'/../../config/routes.conf.php';
            $buildRoute = new BuildRouterStack();
            $buildRoute->setRoutes($routes);
            $buildRoute->build($router);
        }
    }
}
