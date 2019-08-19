<?php
namespace Module\OAuth2
{
    use Module\MongoDriver\Sapi\Feature\iFeatureMongoRepositories;
    use Poirot\Application\Interfaces\Sapi;
    use Poirot\Application\Interfaces\Sapi\iSapiModule;
    use Poirot\Application\ModuleManager\Interfaces\iModuleManager;
    use Poirot\Ioc\Container;
    use Poirot\Loader\Autoloader\LoaderAutoloadAggregate;
    use Poirot\Loader\LoaderNamespaceStack;
    use Poirot\Router\BuildRouterStack;
    use Poirot\Router\Interfaces\iRouterStack;
    use Poirot\Std\Interfaces\Struct\iDataEntity;


    class Module implements iSapiModule
        , Sapi\Module\Feature\iFeatureModuleAutoload
        , Sapi\Module\Feature\iFeatureModuleInitModuleManager
        , Sapi\Module\Feature\iFeatureModuleMergeConfig
        , Sapi\Module\Feature\iFeatureModuleNestServices
        , Sapi\Module\Feature\iFeatureModuleNestActions
        , Sapi\Module\Feature\iFeatureOnPostLoadModulesGrabServices
        , iFeatureMongoRepositories
    {
        const REALM = 'module.oauth2.default_authenticator';


        /**
         * @inheritdoc
         */
        function initAutoload(LoaderAutoloadAggregate $baseAutoloader)
        {
            $nameSpaceLoader = \Poirot\Loader\Autoloader\LoaderAutoloadNamespace::class;
            /** @var \Poirot\Loader\Autoloader\LoaderAutoloadNamespace $nameSpaceLoader */
            $nameSpaceLoader = $baseAutoloader->loader($nameSpaceLoader);
            $nameSpaceLoader->addResource(__NAMESPACE__, __DIR__);

            require_once __DIR__ . '/_functions.php';
        }

        /**
         * @inheritdoc
         */
        function initModuleManager(iModuleManager $moduleManager)
        {
            // Authorization Module Is Required.
            if (! $moduleManager->hasLoaded('Authorization') )
                $moduleManager->loadModule('Authorization');

            // MongoDriver Module Is Required.
            if (! $moduleManager->hasLoaded('MongoDriver') )
                $moduleManager->loadModule('MongoDriver');

            // Load OAuth2 Client To Assert Tokens.
            if (! $moduleManager->hasLoaded('OAuth2Client') )
                $moduleManager->loadModule('OAuth2Client');

            // SMS Client Module Is Required.
            if (! $moduleManager->hasLoaded('SmsClients') )
                $moduleManager->loadModule('SmsClients');

        }

        /**
         * @inheritdoc
         */
        function initConfig(iDataEntity $config)
        {
            return \Poirot\Config\load(__DIR__ . '/../config/mod-oauth2_server');
        }

        /**
         * @inheritdoc
         */
        function getServices(Container $moduleContainer = null)
        {
            return include __DIR__ . '/../config/services.conf.php';
        }

        /**
         * @inheritdoc
         */
        function getActions()
        {
            return include __DIR__ . '/../config/actions.conf.php';
        }

        /**
         * @inheritdoc
         * @param iRouterStack $router
         * @throws \Exception
         */
        function resolveRegisteredServices($viewModelResolver = null, $router = null)
        {
            # Attach Pages To View Resolver:
            if ($viewModelResolver) {
                /** @var LoaderNamespaceStack $resolver */
                $resolver = $viewModelResolver->loader(LoaderNamespaceStack::class);
                $resolver->with([
                    'main/oauth/'  => __DIR__ . '/../view/main/oauth',
                    'error/oauth/' => __DIR__ . '/../view/error/oauth',
                ]);
            }

            # Register Http Routes:
            if ($router) {
                $routes = include __DIR__ . '/../config/routes.conf.php';
                $buildRoute = new BuildRouterStack();
                $buildRoute->setRoutes($routes);
                $buildRoute->build($router);
            }
        }

        /**
         * Return Available Mongo Repositories
         *
         * @return array
         */
        function registerMongoRepositories()
        {
            return include __DIR__ . '/../config/mongo-repositories.conf.php';
        }
    }
}
