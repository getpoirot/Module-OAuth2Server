<?php
namespace Module\OAuth2
{
    use Module\OAuth2\Services\BuildServices;

    use Poirot\Application\Interfaces\Sapi\iSapiModule;
    use Poirot\Application\ModuleManager\Interfaces\iModuleManager;
    use Poirot\Application\Interfaces\Sapi;
    use Poirot\Application\Sapi\Module\ContainerForFeatureActions;

    use Poirot\Ioc\Container;
    use Poirot\Ioc\Container\BuildContainer;

    use Poirot\Loader\Autoloader\LoaderAutoloadAggregate;
    use Poirot\Loader\Autoloader\LoaderAutoloadNamespace;
    use Poirot\Loader\Interfaces\iLoaderAutoload;

    use Poirot\Router\BuildRouterStack;
    use Poirot\Router\Interfaces\iRouterStack;

    use Poirot\Std\Interfaces\Struct\iDataEntity;


    class Module implements iSapiModule
        , Sapi\Module\Feature\iFeatureModuleAutoload
        , Sapi\Module\Feature\iFeatureModuleInitModuleManager
        , Sapi\Module\Feature\iFeatureModuleMergeConfig
        , Sapi\Module\Feature\iFeatureModuleNestActions
        , Sapi\Module\Feature\iFeatureModuleNestServices
        , Sapi\Module\Feature\iFeatureOnPostLoadModulesGrabServices
    {
        const CONF_KEY      = 'module.oauth2';
        const AUTHENTICATOR = 'module.oauth2.default_authenticator';


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
            $nameSpaceLoader->addResource(__NAMESPACE__, __DIR__);

            require_once __DIR__.'/_functions.php';
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
            // ( ! ) ORDER IS MANDATORY

            if (!$moduleManager->hasLoaded('MongoDriver'))
                // MongoDriver Module Is Required.
                $moduleManager->loadModule('MongoDriver');

            if (!$moduleManager->hasLoaded('Authorization'))
                // Authorization Module Is Required.
                $moduleManager->loadModule('Authorization');

            if (!$moduleManager->hasLoaded('OAuth2Client'))
                // Load OAuth2 Client To Assert Tokens.
                $moduleManager->loadModule('OAuth2Client');

            if (!$moduleManager->hasLoaded('SmsClients'))
                // SMS Client Module Is Required.
                $moduleManager->loadModule('SmsClients');

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
            return \Poirot\Config\load(__DIR__ . '/../../config/mod-oauth2_server');
        }

        /**
         * Get Nested Module Services
         *
         * it can be used to manipulate other registered services by modules
         * with passed Container instance as argument.
         *
         * priority not that serious
         *
         * @param Container $moduleContainer
         *
         * @return null|array|BuildContainer|\Traversable
         */
        function getServices(Container $moduleContainer = null)
        {
            $conf    = \Poirot\Config\load(__DIR__ . '/../../config/mod-oauth2_server_services');

            $builder = new BuildServices;
            $builder->with($builder::parseWith($conf));
            return $builder;
        }

        /**
         * Get Action Services
         *
         * priority: after GrabRegisteredServices
         *
         * - return Array used to Build ModuleActionsContainer
         *
         * @return array|ContainerForFeatureActions|BuildContainer|\Traversable
         */
        function getActions()
        {
            return \Poirot\Config\load(__DIR__ . '/../../config/mod-oauth2_server_actions');
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
         * @param iRouterStack $router
         *
         * @internal param null $services service names must have default value
         */
        function resolveRegisteredServices(
            $router = null
        ) {
            # Register Http Routes:
            if ($router) {
                $routes = include __DIR__ . '/../../config/mod-oauth2_server_routes.conf.php';
                $buildRoute = new BuildRouterStack();
                $buildRoute->setRoutes($routes);
                $buildRoute->build($router);
            }
        }
    }
}


namespace Module\OAuth2\Actions
{

    use Module\OAuth2\Actions\Recover\SigninChallengePage;
    use Module\OAuth2\Actions\Recover\SigninNewPassPage;
    use Module\OAuth2\Actions\Recover\SigninRecognizePage;
    use Module\OAuth2\Actions\User\LoginPage;
    use Module\OAuth2\Actions\User\LogoutPage;
    use Module\OAuth2\Actions\User\RegisterPage;
    use Module\OAuth2\Actions\User\RetrieveAuthenticatedUser;
    use Module\OAuth2\Actions\Validation\ValidatePage;
    use Module\OAuth2\Actions\Validation\ResendAuthCodeRequest;
    use Module\OAuth2\Actions\Validation\Validation;
    use Module\OAuth2\Model\Entity\UserEntity;


    /**
     * @property RegisterPage                   $RegisterPage
     * @property LoginPage                      $LoginPage
     * @property LogoutPage                     $LogoutPage
     * @property ValidatePage                   $ValidatePage
     * @property SigninRecognizePage            $SigninRecognizePage
     * @property SigninChallengePage            $SigninChallengePage
     * @property SigninNewPassPage              $SigninNewPassPage
     * @property ResendAuthCodeRequest          $ResendAuthCodeRequest
     * @property RetrieveAuthenticatedUser      $RetrieveAuthenticatedUser
     *
     * @method static UserEntity  RetrieveAuthenticatedUser()
     * @method static Validation  Validation()
     */
    class IOC extends \IOC
    { }
}

namespace Module\OAuth2\Services
{
    /**
     */
    class IOC extends \IOC
    { }
}

namespace Module\OAuth2\Services\Repository
{
    use Module\OAuth2\Interfaces\Model\Repo\iRepoUsers;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAccessTokens;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoAuthCodes;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoClients;
    use Poirot\OAuth2\Interfaces\Server\Repository\iRepoRefreshTokens;


    /**
     * @method static iRepoClients       Clients(array $options=null)
     * @method static iRepoUsers         Users(array $options=null)
     * @method static iRepoAccessTokens  AccessTokens(array $options=null)
     * @method static iRepoRefreshTokens RefreshTokens(array $options=null)
     * @method static iRepoAuthCodes     AuthCodes(array $options=null)
     */
    class IOC extends \IOC
    { }
}
